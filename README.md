# Lead Horizon CSV Generator

Dieses Script erzeugt eine CSV Datei für jede Klasse zum Upload in die Lead Horizon Covidtestungsplattform

## Schritt 1: Export aus Sokrates

1. Login auf https://www.sokrates-bund.at/SOKB/
2. Button "Laufendes Schuljahr" auswählen
3. "Dynamische Suche"
4. "Name der Abfrage" ändern auf "111 Schüler (Erziehungsberechtigte)"
5. Auswahl der Punkte wie in der folgenden Liste
6. Button "Ausführen" drücken
7. Button "Exportieren(CSV)"
8. Liste im selben Ordner wie das convert.php script speichern und `Liste.csv` benennen

Auszuwählende Punkte:

- Klasse
- Schülerkennzahl
- Familienname
- Vorname
- Sozialversicherungsnummer
- Geschlecht
- Geburtsdatum
- PLZ
- Ort
- Straße
- Hausnummer
- Mailadresse
- TelefonNr 1

## Optional: Liste mit Schülerkennzahl und Email aus Active Directory

Falls man die Schülerkennzahl im Active Directory gespeichert hat, kann man diesen Schritt ausfüllen, damit die Email Adressen der SchülerInnen im CSV aufscheinen, statt jene der Eltern aus dem Sokrates.

Hierzu einfach am Domänencontroller folgenden Befehl in einem (als Administrator gestartete) Powershell Fenster ausführen. In dem Beispiel ist die Schülerkennzahl in dem Attribut "employeeID" im Benutzerobjekt gespeichert. Wenn man es in einem anderen Attribut gespeichert hat, kann man es einfach austauschen.

```powershell
Get-ADUser -Filter "*" -Properties EmailAddress,EmployeeID | where {$_.EmailAddress -ne $null -and $_.EmployeeId -ne $null} | Select EmailAddress,EmployeeId  | Export-Csv -NoTypeInformation -Delimiter "," -Path email-employeeid.csv
```

Die erzeugte Datei `email-employeeid.csv` auch in den selben Ordne wie das php script speichern

## Schritt 2: Script laufen lassen

Windows: C:\pfad\zur\php.exe convert.php

Linux: php convert.php

Danach wird im `output` Ordner eine CSV Datei für jede Klasse erstellt, die direkt auf Lead Horizon importiert werden kann