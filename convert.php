<?php

//einstellungen
//layout der CSV datei wird hier festgelegt
//wenn man sich an die Anleitung im README.md gehalten hat, 
//sollte hier alles passen und man muss nichts ändern

$csv_index_klasse = 0;
$csv_index_kennzahl = 1; //schülerkennzahl
$csv_index_nachname = 2;
$csv_index_vorname = 3;
$csv_index_sozvers = 4;
$csv_index_geschl = 5;
$csv_index_geburt = 6;
$csv_index_plz = 7;
$csv_index_ort = 8;
$csv_index_strasse = 9;
$csv_index_hausnummer = 10;
$csv_index_email = 11;
$csv_index_tel = 12;

// wie lautet der Name der CSV vom Sokrates Export?
$in = 'Liste.csv';


//CSV Layout wie es Lead Horizon möchte
$outheader = [
    'Email',
    'First Name',
    'Last Name',
    'Gender',
    'Phone Number',
    'Social Security Number',
    'ZIP',
    'City',
    'Street',
    'Birth Date'
];

$datennachklasse = [];
$uniquenames = [];
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));
$lines = file(ROOT.DS.$in);

foreach ($lines as $index => $line) {
    if ($index == 0) continue;
    $a = explode(";", trim($line));
    $klasse = $csv_index_klasse;
    $kennzahl = $csv_index_kennzahl;
    $nachname = $csv_index_nachname;
    $vorname = $csv_index_vorname;
    $sozvers = $csv_index_sozvers;
    $geschl = $csv_index_geschl;
    $geburt = $csv_index_geburt;
    $plz = $csv_index_plz;
    $ort = $csv_index_ort;
    $strasse = $csv_index_strasse;
    $hausnummer = $csv_index_hausnummer;
    $email = findEmail($kennzahl)?:$csv_index_email;
    $tel = $csv_index_tel;

    if(!$kennzahl) exit("$vorname $nachname ($klasse) hat keine kennzahl");

    if($uniquenames[$kennzahl])
        continue;
    $uniquenames[$kennzahl] = true;

    //edits
    if(!$sozvers)
        $sozvers = '1234567890';
    $klasse = strtoupper($klasse);
    $geschl = ($geschl == 'm' ? 'male' : 'female');

    if($tel)
    {
        $tel = filternum($tel);
    
        if (startsWith($tel, '+'))
            $tel = str_replace('+', '00', substr($tel, 0, strpos($tel, ' ') - 1));
        else {
            if ($tel[0] == '0')
                $tel = substr($tel, 1);
            $tel = '0043' . $tel;
        }
    }
    else
    {
        echo "[i] Keine Tel: $nachname $vorname ($klasse)\n";
        $tel = '00430000'.rand(111111,999999);
    }

    if(strlen($tel)>15) substr($tel,0,15);

    $geburt = implode("-", array_reverse(explode('.', $geburt)));
    $vorname = ucfirst(cleanNames(mb_strtolower($vorname)));
    $nachname = ucfirst(cleanNames(mb_strtolower($nachname)));

    

    $diesedaten = [
        $email,
        $vorname,
        $nachname,
        $geschl,
        $tel,
        $sozvers,
        $plz,
        $ort,
        $strasse . ' ' . $hausnummer,
        $geburt
    ];

    $datennachklasse[$klasse][] = $diesedaten;
}

ksort($datennachklasse);

foreach ($datennachklasse as $klasse => $daten) {
    if(!is_dir(ROOT.DS.'output'))
        mkdir(ROOT.DS.'output');
    $fp = fopen(ROOT."output".DS."$klasse.csv", 'w');

    //zunächst den header schreiben
    fwrite($fp, implode(';', $outheader) . "\n");

    //jetzt alle Daten
    foreach ($daten as $d) {
        fwrite($fp, implode(';', $d) . "\n");
    }

    fclose($fp);
}



// just a few helpers
function filteralphanum($text)
{
    return preg_replace("/[^A-Za-z0-9]/", '', $text);
}
function filteralpha($text)
{
    return preg_replace("/[^A-Za-z]/", '', $text);
}
function filternum($text)
{
    return preg_replace("/[^0-9\+]/", '', $text);
}
function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return substr($haystack, 0, $length) === $needle;
}
function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if (!$length) {
        return true;
    }
    return substr($haystack, -$length) === $needle;
}
function cleanNames($name)
{
    $convert_to = array(
        "a", "a", "a", "a", "ae", "a", "ae", "c","c","c", "e", "e", "e", "e", "i", "i", "i", "i",
        "o", "n", "o", "o", "o", "o", "oe", "o", "u", "u", "u", "ue", "y",'l'
    );
    $convert_from = array(
        "à", "á", "â", "ã", "ä", "å", "æ", "ç","č","ć", "è", "é", "ê", "ë", "ì", "í", "î", "ï",
        "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý","ł"
    );

    $text = str_replace($convert_from, $convert_to, $name);


    return filteralpha($text);
}

function deepLower($texto)
{
    $texto = strtr($texto, " 
        ACELNÓSZZABCDEFGHIJKLMNOPRSTUWYZQ 
        XV
        ÂÀÁÄÃÊÈÉËÎÍÌÏÔÕÒÓÖÛÙÚÜÇ 
        ", " 
        acelnószzabcdefghijklmnoprstuwyzq 
        xv
        aaaäaeeeeiiiiooooöuuuüc 
        ");
    return strtolower($texto);
}

function findEmail($skz)
{
    if(!file_exists('email-employeeid.csv') || !trim($skz)) return false;
    $lines = file('email-employeeid.csv');

    foreach($lines as $line)
    {
        if(strpos($line,$skz))
        {
            $a = explode(",", str_replace('"','',trim($line)));
            if($a[1]==$skz)
                return $a[0];
        }
    }
}