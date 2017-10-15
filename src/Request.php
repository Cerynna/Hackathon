<?php
/**
 * Created by PhpStorm.
 * User: hysterias
 * Date: 12/10/17
 * Time: 18:30
 */

namespace FJA;


class Request
{
    /**
     * @var
     */
    public $user;
    /**
     * @var mixed
     */
    public $arguments;


    /**
     * Request constructor.
     * @param $user
     * @param $arguments
     */
    public function __construct($user, $arguments)
    {
        $this->user = $user;
        $this->arguments = unserialize($arguments);
    }

    /**
     * @param $type
     * @param $request
     * @return mixed
     */
    public function recupArray($type, $request)
    {
        $filename = "cache/$this->user.$type.log";
        if (!file_exists($filename)) {
            $array = $this->recupCurl("$request");
            file_put_contents("$filename", serialize($array));
            return $array;
        } else {
            $time = filemtime("$filename");
            $maintenant = time();
            $ecart = round(($maintenant - $time) / 60);
            if ($ecart >= 5) {
                $array = $this->recupCurl("$request");
                file_put_contents("$filename", serialize($array));
                return $array;
            } else {
                return unserialize(file_get_contents("$filename"));
            }

        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function recupCurl($request)
    {
        $token = file_get_contents("src/config.php");
        $connect = curl_init();
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_HEADER, 0);
        curl_setopt($connect, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13');
        $this_header = array("Content-Type: application/json", "Authorization: token $token",);
        curl_setopt($connect, CURLOPT_HTTPHEADER, $this_header);
        $url = $request;
        curl_setopt($connect, CURLOPT_URL, $url);
        $data = curl_exec($connect);
        $array = json_decode($data);
        curl_close($connect);

        return $array;
    }


    /**
     * @param $affichExtend
     * @return string
     */
    public function snippetsLite($affichExtend)
    {
        $arrayUser = $this->recupArray('user', "https://api.github.com/users/$this->user");
        foreach ($arrayUser as $key => $value) {
            if (in_array($key, $this->arguments["user"])) {
                $arrayFinal["user"][$key] = $value;
            }
        }
        $arrayRepos = $this->recupArray('repos', "$arrayUser->repos_url");
        $limitRepos = $this->arguments['repos']['limit'];
                    for ($i = 0; $i < $limitRepos; $i++) {
                $arrayFinal['repos'][$i] = $arrayRepos[$i];
            }

        /**
         * ON CREE LA DIV de BASE !!
         */
        $returnDiv = "";
        $returnDiv .= "<div class=\"app z-depth-4\">" . PHP_EOL;
        $returnDiv .= "<div class=\"appHeader\">" . PHP_EOL;
        if (in_array("avatar_url", $this->arguments["user"])) {
            $returnDiv .= "<img src=\"" . $arrayUser->avatar_url . "\" alt=\"imgProfil\" class=\"circle\" width=\"120px\" height=\"120px\">" . PHP_EOL;
        }
        $returnDiv .= "<div class=\"infos\">" . PHP_EOL;
        if (in_array("login", $this->arguments["user"])) {
            $returnDiv .= "<span class=\"userName\">@" . $arrayUser->login . "</span>" . PHP_EOL;
        }
        $returnDiv .= "<div class=\"appFollow\">" . PHP_EOL;
        if (in_array("followers", $this->arguments["user"])) {
            $returnDiv .= "<span class=\"followers chip amber white-text\">Followers : " . $arrayUser->followers . "</span>" . PHP_EOL;
        }
        $returnDiv .= "<br>" . PHP_EOL;
        if (in_array("following", $this->arguments["user"])) {
            $returnDiv .= "<span class=\"following chip amber white-text\">Following : " . $arrayUser->following . "</span>" . PHP_EOL;
        }
        $returnDiv .= "</div></div></div>" . PHP_EOL;
        $returnDiv .= "<div class=\"divider\"></div>" . PHP_EOL;
        $returnDiv .= "<div class=\"appRepos\">" . PHP_EOL;
        $returnDiv .= "<div class=\"countCreate\">" . PHP_EOL;
        if (in_array("public_repos", $this->arguments["user"])) {
            $returnDiv .= "<p class=\"countRepos chip blue white-text\">Depots : " . $arrayUser->public_repos . "</p>" . PHP_EOL;
        }
        if (in_array("public_gists", $this->arguments["user"])) {
            $returnDiv .= "<p class=\"countGists chip green white-text\">Gists : " . $arrayUser->public_gists . "</p>" . PHP_EOL;
        }
        $returnDiv .= "</div>" . PHP_EOL;
        if (in_array("show", $this->arguments['repos'])) {
            $returnDiv .= "<div class=\"divider\"></div>" . PHP_EOL;
            $returnDiv .= "<div class=\"deposApp\">" . PHP_EOL;
            $returnDiv .= "<span>Les derniers depos :</span>" . PHP_EOL;
            $returnDiv .= "<ul>" . PHP_EOL;
            foreach ($arrayFinal['repos'] as $key => $arrayOneRepos) {
                $returnDiv .= "<li><i class=\"material-icons blue-text\">folder</i> " . $arrayOneRepos->name . "</li>" . PHP_EOL;
            }
            $returnDiv .= "</ul>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
        }
        $returnDiv .= "</div>" . PHP_EOL;
        if ($affichExtend == TRUE) {

            $returnDiv .= "<div class=\"appFooter center\">" . PHP_EOL;
            $returnDiv .= "<div class=\"divider\"></div>" . PHP_EOL;
            $returnDiv .= "<a class=\"waves-effect waves-light btn modal-trigger amber white-text\" href=\"#modal1\">Click here for more details</a>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
        }
        $returnDiv .= "</div>" . PHP_EOL;
        //$returnDiv .= "";
        return $returnDiv;
    }
    /**
     * @return string
     */
    public function snippetsFat()
    {
        $returnDiv = "";
        $arrayUser = $this->recupArray('user', "https://api.github.com/users/$this->user");
        $arrayRepos = $this->recupArray('repos', "$arrayUser->repos_url");
        $linkGists = preg_replace("/(\{.*?\})/", "", $arrayUser->gists_url);
        $arrayGists = $this->recupArray('gists', "$linkGists");
        /**
         * ON CREE LA DIV de l'EXTENT !!
         */
        $returnDiv .= "<div id=\"modal1\" class=\"modal bottom-sheet\">" . PHP_EOL;
        $returnDiv .= "<div class=\"modal-header\">" . PHP_EOL;
        // var_dump($arrayFinal);
        $returnDiv .= "<h4>Détails du compte github de @" . $this->user . "</h4>" . PHP_EOL;
        $returnDiv .= "<a href=\"#!\" class=\"modal-action modal-close waves-effect waves-green btn-flat\"><i class=\"material-icons\">close</i></a>" . PHP_EOL;
        $returnDiv .= "</div>" . PHP_EOL;
        $returnDiv .= "<div class=\"modal-content\">" . PHP_EOL;
        $returnDiv .= "<ul id=\"tabs-swipe-demo\" class=\"tabs tabs-fixed-width\">" . PHP_EOL;
        $returnDiv .= "<li class=\"tab\"><a href=\"#test-swipe-1\">repos</a></li>" . PHP_EOL;
        $returnDiv .= "<li class=\"tab\"><a href=\"#test-swipe-2\">gists</a></li>" . PHP_EOL;
        $returnDiv .= "</ul>" . PHP_EOL;
        $returnDiv .= "<div id=\"test-swipe-1\" class=\"col s12 slideDetails\">" . PHP_EOL;
        $returnDiv .= "<ul class=\"collapsible popout\" data-collapsible=\"accordion\">" . PHP_EOL;
        foreach ($arrayRepos as $key => $arrayOneRepos) {
            $returnDiv .= "<li>";
            $returnDiv .= "<div class=\"collapsible-header hoverable blue white-text\">";
            $returnDiv .= "<div class=\"container-fluid\">";
            $returnDiv .= "<div class=\"row\">";
            $returnDiv .= "<div>";
            $returnDiv .= "<h5><i class=\"material-icons\">folder</i>" . $arrayOneRepos->name . "</h5>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "<div class=\"row\">";
            $returnDiv .= "<div>" . PHP_EOL;
            $returnDiv .= "<span class=\"lastCommit\">Last updated : " . $arrayOneRepos->pushed_at . "</span>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "<div class=\"collapsible-body\">" . PHP_EOL;
            $returnDiv .= "<div class=\"center\">" . PHP_EOL;
            $returnDiv .= "Lien du dépôt : <a href='" . $arrayOneRepos->html_url . "'>" . $arrayOneRepos->html_url . "</a>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "<div class=\"collapsible-footer\">" . PHP_EOL;
            if ($arrayOneRepos->language !== NULL) {
                $returnDiv .= "<div class=\"chip red white-text\">" . PHP_EOL;
                $returnDiv .= $arrayOneRepos->language . PHP_EOL;
                $returnDiv .= "</div>" . PHP_EOL;
            }
            $returnDiv .= "<span>" . $arrayOneRepos->forks . " Forks</span>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "</li>" . PHP_EOL;
        }
        $returnDiv .= "</ul>" . PHP_EOL;
        $returnDiv .= "</div>" . PHP_EOL;
        $returnDiv .= "<div id=\"test-swipe-2\" class=\"col s12 slideDetails\">" . PHP_EOL;
        $returnDiv .= "<ul class=\"collapsible popout\" data-collapsible=\"accordion\">" . PHP_EOL;
        foreach ($arrayGists as $key => $arrayOneGists) {
            $returnDiv .= "<li>" . PHP_EOL;
            $returnDiv .= "<div class=\"collapsible-header hoverable green white-text\">" . PHP_EOL;
            foreach ($arrayOneGists->files as $name => $arrayInfoGist) {
                $language = $arrayInfoGist->language;
            }
            $returnDiv .= "<div class=\"container-fluid\">";
            $returnDiv .= "<div class=\"row\">";
            $returnDiv .= "<div>" . PHP_EOL;
            $returnDiv .= "<h5><i class=\"material-icons\">description</i>" . $name . "</h5>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "<div class=\"row\">";
            $returnDiv .= "<div>";
            $returnDiv .= "<span class=\"lastCommit\">Last updated : " . $arrayOneGists->created_at . "</span>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "<div class=\"collapsible-body\">" . PHP_EOL;
            $returnDiv .= "<div class=\"center\">" . PHP_EOL;
            $returnDiv .= "Lien du gist : <a href='" . $arrayOneGists->html_url . "'>" . $arrayOneGists->html_url . "</a>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "<div class=\"collapsible-footer\">" . PHP_EOL;
            if ($language !== NULL) {
                $returnDiv .= "<div class=\"chip red white-text\">" . PHP_EOL;
                $returnDiv .= $language . PHP_EOL;
                $returnDiv .= "</div>" . PHP_EOL;
            }
            $returnDiv .= "<span>" . $arrayOneGists->forks . "</span>" . PHP_EOL;
            $returnDiv .= "</div>" . PHP_EOL;
            $returnDiv .= "</li>" . PHP_EOL;
        }
        $returnDiv .= "</ul>" . PHP_EOL;
        $returnDiv .= "</div>" . PHP_EOL;
        $returnDiv .= "</div>" . PHP_EOL;
        $returnDiv .= "</div>" . PHP_EOL;
        //$returnDiv .= "";
        return $returnDiv;
    }
}







