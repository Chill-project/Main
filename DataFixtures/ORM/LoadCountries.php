<?php

namespace Chill\MainBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Acme\HelloBundle\Entity\UserGroup;

/**
 * Load countries into database
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class LoadCountries extends AbstractFixture {
    
    public function getOrder() {
        return 1001;
    }
    
    public function load(ObjectManager $manager) {
        
        echo "loading countries... \n";
        
        foreach ($this->countries as $code => $name) {
            $country = new \Chill\MainBundle\Entity\Country();
            $country->setLabel(ucwords($name));
            $manager->persist($country);
        }
        
        $manager->flush();
    }
    
    
    public $countries = array(
        "ad" => "andorre",
        "ae" => "emirats arabes unis",
        "af" => "afghanistan",
        "ag" => "antigua-et-barbuda",
        "ai" => "anguilla",
        "al" => "albanie",
        "am" => "arménie",
        "an" => "antilles",
        "ao" => "angola",
        "aq" => "antarctique",
        "ar" => "argentine",
        "as" => "samoa américaines",
        "at" => "autriche",
        "au" => "australie",
        "aw" => "aruba",
        "az" => "azerbaïdjan",
        "ba" => "bosnie-herzégovine",
        "bb" => "barbade",
        "bd" => "bangladesh",
        "be" => "belgique",
        "bg" => "bulgarie",
        "bh" => "bahreïn",
        "bi" => "burundi",
        "bj" => "bénin ",
        "bm" => "bermudes",
        "bn" => "brunei darussalam",
        "bo" => "bolivie",
        "br" => "brésil",
        "bs" => "bahamas",
        "bt" => "bhoutan",
        "bw" => "botswana",
        "by" => "biélorussie",
        "bz" => "belize",
        "kh"=>"cambodge",
        "ca" => "canada",
        "cc" => "iles cocos",
        "cd" => "république démocratique du congo",
        "cf" => "république centrafricaine",
        "cg" => "congo",
        "ch" => "suisse",
        "ci" => "côte d'ivoire",
        "ck" => "iles cook",
        "cl" => "chili",
        "cm" => "cameroun",
        "cn" => "chine",
        "co" => "colombie",
        "cr" => "costa rica","cu" => "cuba",
        "cv" => "cap-vert",
        "cx" => "ile christmas",
        "cy" => "chypre",
        "cz" => "république tchèque",
        "de" => "allemagne",
        "dj" => "djibouti",
        "dk" => "danemark",
        "dm" => "dominique",
        "do" => "république dominicaine",
        "dz" => "algérie",
        "ec" => "equateur",
        "ee" => "estonie",
        "eg" => "egypte",
        "eh" => "sahara occidental",
        "er" => "erythrée",
        "es" => "espagne",
        "et" => "ethiopie",
        "fi" => "finlande",
        "fj" => "fidji",
        "fk" => "iles falklands",
        "fm" => "micronésie",
        "fo" => "ile feroe",
        "fr" => "france",
        "ga" => "gabon",
        "gd" => "grenade",
        "ge" => "géorgie",
        "gf" => "guyane française",
        "gh" => "ghana",
        "gi" => "gibraltar",
        "gl" => "groënland",
        "gq"=>"guinée équatoriale",
        "gm" => "gambie",
        "gn" => "guinée",
        "gp" => "guadeloupe",
        "gr" => "grèce",
        "gt" => "guatemala",
        "gu" => "guam",
        "gw" => "guinée-bissao",
        "gy" => "guyane",
        "hk" => "hong kong",
        "hn" => "honduras",
        "hr" => "croatie",
        "ht" => "haïti",
        "hu" => "hongrie",
        "id" => "indonésie",
        "ie" => "irlande",
        "il" => "israël",
        "in" => "inde",
        "iq" => "iraq",
        "ir" => "iran",
        "is" => "islande",
        "it" => "italie",
        "jm" => "jamaïque",
        "jo" => "jordanie",
        "jp" => "japon",
        "ke" => "kenya",
        "kg" => "kirghistan",
        "bf" => "burkina faso",
        "ki" => "kiribati",
        "km" => "république comorienne",
        "kn" => "saint-christophe-et-niévès",
        "kp" => "corée du nord",
        "kr" => "corée du sud",
        "kw" => "koweït",
        "ky" => "iles caïmans",
        "kz" => "kazakhstan",
        "la" => "laos",
        "lb" => "liban",
        "lc" => "sainte-lucie",
        "li" => "liechtenstein",
        "lk" => "sri lanka",
        "lr" => "libéria",
        "ls" => "lesotho",
        "lt" => "lituanie",
        "lu" => "luxembourg",
        "lv" => "lettonie",
        "ly" => "libye",
        "ma" => "maroc",
        "mc" => "monaco",
        "md" => "moldavie",
        "mg" => "madagascar",
        "ml"=>"mali",
        "mh" => "marshall",
        "mk" => "macédoine","mm"=>"myanmar",
        "mq"=>"martinique",
        "mn" => "mongolie",
        "mo" => "makau",
        "mp" => "ile mariana du nord",
        "mr" => "mauritanie",
        "ms" => "monteserrat",
        "mu" => "maurice",
        "mt"=>"malte",
        "mv" => "maldives",
        "mw" => "malawi",
        "mx" => "mexique west",
        "my" => "malaisie",
        "mz" => "mozambique",
        "na" => "namibie",
        "nc" => "nouvelle-calédonie",
        "ne" => "niger",
        "nf" => "ile de norfolk",
        "ng" => "nigeria",
        "ni" => "nicaragua",
        "nl" => "pays-bas",
        "no" => "norvège",
        "np" => "népal",
        "nr" => "nauru",
        "nu" => "niue",
        "nz" => "nouvelle-zélande",
        "om" => "oman",
        "pa" => "panama",
        "pe" => "pérou",
        "pf" => "polynésie française",
        "pg" => "papouasie - nouvelle guinée",
        "ph" => "philippines",
        "pk" => "pakistan",
        "pl" => "pologne",
        "pm" => "st. pierre and miquelon",
        "pn" => "pitcairn",
        "pr" => "porto rico",
        "ps" => "palestine",
        "pt" => "portugal",
        "pw" => "palau",
        "py" => "paraguay",
        "qa" => "qatar",
        "re" => "réunion",
        "ro" => "roumanie",
        "ru" => "fédération russe",
        "rw" => "rwanda",
        "sa" => "arabie saoudite",
        "sb" => "iles salomon",
        "sc" => "seychelles",
        "sd" => "soudan",
        "se" => "suède",
        "sg" => "singapour",
        "sh" => "saint hélène",
        "si" => "slovénie",
        "sk" => "slovaquie",
        "sl" => "sierra leone",
        "sm" => "saint-marin",
        "sn" => "sénégal",
        "so" => "somalie",
        "sr" => "suriname",
        "st" => "sao tomé-et-principe",
        "sv" => "salvador",
        "sy" => "syrie",
        "sz" => "swaziland",
        "tc" => "turks et caicos",
        "td" => "république du tchad",
        "tg" => "togo",
        "th" => "thaïlande",
        "tj" => "tchétchénie",
        "tk" => "iles tokelau",
        "tm" => "turkménistan",
        "tn" => "tunisie",
        "to" => "tonga",
        "tp" => "timor-oriental",
        "tr" => "turquie",
        "tt" => "trinité-et-tobago",
        "tv" => "tuvalu",
        "tw" => "taiwan",
        "tz" => "tanzanie",
        "ua" => "ukraine",
        "ug" => "ouganda",
        "gb" => "royaume-uni",
        "us" => "etats unis d'amérique",
        "uy" => "uruguay",
        "uz" => "ousbékistan",
        "va" => "vatican",
        "vc" => "saint-vincent-et-les grenadines",
        "ve" => "vénézuela",
        "vg" => "iles vierges américaines",
        "vi" => "iles vierges britanniques ",
        "vn" => "viêt-nam",
        "vu" => "vanuatu",
        "wf" => "wallis et futuna",
        "ws" => "samoa occidentales",
        "ye" => "yémen",
        "yt" => "mayotte",
        "yu" => "yougoslavie",
        "za" => "afrique du sud",
        "zm" => "zambie",
        "zw" => "zimbabwe"
        );

}
