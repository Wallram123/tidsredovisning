<?php

declare (strict_types=1);
require_once '../src/activities.php';

/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaActivityTester(): string {
    // Kom ihåg att lägga till alla funktioner i filen!
    $retur = "";
    $retur .= test_HamtaAllaAktiviteter();
    $retur .= test_HamtaEnAktivitet();
    $retur .= test_SparaNyAktivitet();
    $retur .= test_UppdateraAktivitet();
    $retur .= test_RaderaAktivitet();

    return $retur;
}

/**
 * Tester för funktionen hämta alla aktiviteter
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaAllaAktiviteter(): string {
    $retur = "<h2>test_HamtaAllaAktiviteter</h2>";
    try {
        $svar=hamtaAllaAktiviteter();
        if($svar->getStatus()===200){
            $retur .="<p class='ok'>Hämta alla aktiviteter lyckades" . count($svar->getContent() )
                    ."poster returnerades</>";
        }else{
            $retur .= "<p class='error'>Hämta alla aktiviteter misslyckades<br>"
                    . $svar->getStatus() . " returnerades</p>";
        

    
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Tester för funktionen hämta enskild aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaEnAktivitet(): string {
    $retur = "<h2>test_HamtaEnAktivitet</h2>";
    try {
        // Misslyckat hämta post id=-1
        $svar=hamtaEnskildAktivitet('-1');
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>Hämta post med id=-1 misslyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Hämta post med id=-1 returnerade " .$svar->getStatus()
                . "istället för förväntat 400</p>";
        }
        // Misslyckat hämta post id=0
        $svar=hamtaEnskildAktivitet('0');
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>Hämta post med id=0 misslyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Hämta post med id=0 returnerade " .$svar->getStatus()
                . "istället för förväntat 400</p>";
        }
        // Misslyckat hämta post id=3.14
        $svar=hamtaEnskildAktivitet('3.14');
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>Hämta post med id=3.14 misslyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Hämta post med id=3.14 returnerade " .$svar->getStatus()
                . "istället för förväntat 400</p>";
        }
        // Koppla databas
        $db= connectDb();

        // skapa transaktion
        $db->beginTransaction();

        // skapa ny post för att vara säker på att posten finns
        $svar=SparaNyAktivitet('Aktivitet' . time());
        if($svar->getStatus()===200) {
            $nyttId=$svar->getContent()->id;
        }else{
            throw new Exception('Kunde inte skapa ny post för kontroll');
        }

        // Lyckas hämta skapad post
        $svar=hamtaEnskildAktivitet("$nyttId");
        if($svar->getStatus()===200) {
            $retur .="<p class='ok'>Hämta en aktivitet gick bra</p>";
        }else{
            $retur .="<p class='error'>Hämta en aktivitet misslyckades " .$svar->getStatus()
            . "returnerade istället för förväntat 200</p>";
        }

        // Misslyckades med att hämta post med id +1
        $nyttId++;
        $svar= hamtaEnskildAktivitet("$nyttId");
        if($svar->getStatus()===400) {
        $retur .= "<p class='ok'>Hämta en aktivitet med id som saknas misslyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Hämta en aktivitet med is som saknas misslyckades " .$svar->getStatus()
            . "returnerade istället för förväntat 400</p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        //Återställ databasen
        $db->rollback();
    }

    return $retur;
}

/**
 * Tester för funktionen spara aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_SparaNyAktivitet(): string {
    $retur = "<h2>test_SparaNyAktivitet</h2>";

    $nyAktivitet="Aktivitet" . time();

    try {
    //koppla databas
    $db= connectDb();

    //starta transaktion
    $db->beginTransaction();

    // spara tom aktivitet - misslyckat
    $svar=SparaNyAktivitet('');
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>spara tom aktivitet misslyckades, som förväntat</p>";
    } else {
        $retur .="<p class='ok'>spara tom aktivitet misslyckades, status" . $svar->getStatus()
                . "returnderades istället som förväntat 400</p>";
    }
    //spara ny aktivitet - lyckat
    $svar= sparaNyAktivitet($nyAktivitet);
    if($svar->getStatus()===200) {
        $retur .="<p class='ok'>spara aktivitet lyckades</p>";
    } else {
        $retur .="<p class='ok'>spara tom aktivitet misslyckades, status" . $svar->getStatus()
                . "returnderades istället som förväntat 200</p>";
    }
    //spara ny aktivitet - misslyckat
    $svar= sparaNyAktivitet($nyAktivitet);
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>spara duplicerad aktivitet misslyckades, som förväntat</p>";
    } else {
        $retur .="<p class='ok'>spara tom aktivitet misslyckades, status" . $svar->getStatus()
                . "returnderades istället som förväntat 400</p>";
    }
    // Återställa databasen

    
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
    //återställa databasen
        if($db) {
            $db->rollback();
        }
    }

    return $retur;
}

/**
 * Tester för uppdatera aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_UppdateraAktivitet(): string {
    $retur = "<h2>test_UppdateraAktivitet</h2>";


    try {
        //koppla databas
        $db = connectDb();
        //starta transaktion
        $db->beginTransaction();

    // Misslyckades med att upddatera id=-1
    $svar= UppdateraAktivitet("-1", "Aktivitet");
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>Uppdatea aktivitet med id=-1 misslyckades, som förväntat</p>";
    }else{
    $retur .="<p class='error'>Uppdatea aktivitet med id=-1 misslyckades, status " . $svar->getStatus()
    . "istället för förväntad 400</p>";
    }

    // Misslyckades med att uppdatera id=0
    $svar= UppdateraAktivitet("0", "Aktivitet");
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>Uppdatea aktivitet med id=0 misslyckades, som förväntat</p>";
    }else{
    $retur .="<p class='error'>Uppdatea aktivitet med id=0 misslyckades, status " . $svar->getStatus()
    . "istället för förväntad 400</p>";
    }

    // Misslyckades med att uppdatera id=3.14
    $svar= UppdateraAktivitet("3.14", "Aktivitet");
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>Uppdatea aktivitet med id=3.14 misslyckades, som förväntat</p>";
    }else{
    $retur .="<p class='error'>Uppdatea aktivitet med id=3.14 misslyckades, status " . $svar->getStatus()
    . "istället för förväntad 400</p>";
    }

    // Misslyckades med att uppdatera aktivitet=''
    $svar= UppdateraAktivitet("3", "");
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>Uppdatea aktivitet med tom aktivitet misslyckades, som förväntat</p>";
    }else{
    $retur .="<p class='error'>Uppdatea aktivitet med id=-1 misslyckades, status " . $svar->getStatus()
    . "istället för förväntad 400</p>";
    }

    $aktivitet="aktivitet" . time();
    $svar= sparaNyAktivitet($aktivitet);
    if($svar->getStatus()===200) {
        $nyttId=$svar->getContent()->id;
    } else {
        throw new Exception("spara aktivitet för uppdatering misslyckades");
    }

    $svar= uppdateraAktivitet("$nyttId", $aktivitet);
    if($svar->getStatus()===200 && $svar->getContent()->result===false) {
        $retur .="<p class='ok'> uppdatera aktivitet med samma information misslyckades, som förväntat</p>";
    } else {
        $retur .="<p class='error'>uppdatera aktivitet med samma information misslyckades<br>"
        . "status:" . $svar->getStatus() . " returneras med följande innerhåll:<br>"
        . print_r($svar->getContent(), true) ."</br>";
    }
    

     //lyckades med att upåpdatera aktivitet 
    $svar=UppdateraAktivitet("$nyttId", "NY " . $aktivitet);
    if($svar->getStatus()===200 && $svar->getContent()->result===true) {
    $retur .="<p class='ok'>uppdatera aktivitet lyckades</p>";
    }else{
    $retur .="<p class='error'>uppdatera aktivitet misslyckades<br>"
    . "status:" . $svar->getStatus() . " returneras med följande innerhåll:<br>"
    . print_r($svar->getContent(), true) ."</br>";
    }

    //misslyckas med att uppdatera aktivitet som inte finns
    $nyttId++;
    $svar=UppdateraAktivitet("$nyttId", "What ever");
    if($svar->getStatus()===200 && $svar->getContent()->result===false) {
    $retur .="<p class='ok'>uppdatera aktivitet misslyckades, som förväntat</p>";
    }else{
    $retur .="<p class='error'>uppdatera aktivitet misslyckades<br>"
    . "status:" . $svar->getStatus() . " returneras med följande innerhåll:<br>"
    . print_r($svar->getContent(), false) ."</br>";
    }

    //misslyckas med att uppdatera till en aktivitet som finns
    $svar= sparaNyAktivitet($aktivitet);
    if($svar->getStatus()===200) {
        $nyttId=$svar->getContent()->id;
    } else {
        throw new Exception("spara aktivitet för uppdatering misslyckades");
    }
    $svar=UppdateraAktivitet("$nyttId", "NY " . $aktivitet);
    if($svar->getStatus()===400 ) {
    $retur .="<p class='ok'>uppdatera aktivitet till en redan befintlig misslyckades</p>";
    }else{
    $retur .="<p class='error'>uuppdatera aktivitet till en redan befintlig misslyckades<br>"
    . "status:" . $svar->getStatus() . " returneras med följande innerhåll:<br>"
    . print_r($svar->getContent(), true) ."</br>";
    }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        //återställ databas
        if ($db) {
            $db->rollback();
        }
        
    }

    return $retur;
}

/**
 * Tester för funktionen radera aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_RaderaAktivitet(): string {
    $retur = "<h2>test_RaderaAktivitet</h2>";
    try {
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}
