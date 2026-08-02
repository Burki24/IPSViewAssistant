# IPSView Assistant

Das Modul erstellt neue IPSView-Medienobjekte direkt aus einer übersichtlichen Symcon-Konfiguration. Außerdem kann es das Design einer bestehenden View auf eine separate Kopie übertragen, ohne das Original zu verändern.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Installation](#3-installation)
4. [Neue View erstellen](#4-neue-view-erstellen)
5. [Bestehende View gestalten](#5-bestehende-view-gestalten)
6. [Hinweise](#6-hinweise)

### 1. Funktionsumfang

- Neue View mit Name, Zielkategorie, Hauptseite, Seitenverhältnis und Ausrichtung erstellen
- Designvorlagen: IPSView-Standard, Hell, Dunkel, Warm, Kühl, Erdig, Wasser, Sonnig und Benutzerdefiniert
- Verständlich gruppierte Farben für Hintergründe, Flächen, Texte, Rahmen, Akzente und Statusmeldungen
- Globale Einstellungen für Schatten, Transparenz, Farbverläufe, Schriftgrößen, Schriftarten, Ecken und Rahmen
- Live-Vorschau des gewählten Designs mit offline verfügbaren Vorschauschriften
- Bestehende View als separate Designkopie neu gestalten

### 2. Voraussetzungen

- Symcon ab Version 9.0
- Installierter und lizenzierter IPSView Designer

### 3. Installation

1. Im Symcon Modul-Control die folgende URL hinzufügen:
   `https://github.com/Burki24/IPSViewAssistant`
2. Anschließend eine Instanz **IPSView Assistant** erstellen.

### 4. Neue View erstellen

1. Die Instanzkonfiguration öffnen.
2. Name der View, Zielkategorie und Name der Hauptseite festlegen.
3. Seitenverhältnis und Ausrichtung auswählen.
4. Eine Designvorlage wählen und bei Bedarf Farben, Effekte, Typografie oder Formensprache anpassen.
5. Die Vorschau prüfen und **View erstellen** wählen.
6. Das erzeugte Medienobjekt im Objektbaum öffnen und im IPSView Designer speichern.

### 5. Bestehende View gestalten

1. Unter **Bestehende View** die Quell-IPSView auswählen.
2. Namen und Zielkategorie der Designkopie festlegen.
3. Den Gestaltungsumfang auswählen:
   - **Nur globale Vorgaben** – ändert ausschließlich die zentralen Designwerte.
   - **Passende Bedienelementfarben mitgestalten** – empfohlene Einstellung; ändert nur Farben, die zum bisherigen globalen Design passen.
   - **Alle Grundfarben der Bedienelemente vereinheitlichen** – vereinheitlicht zusätzlich grundlegende Hintergründe, Texte und Rahmen.
4. Das gewünschte Design einstellen und **Designkopie speichern** wählen.

Die Quell-IPSView bleibt unverändert. Weitere Speichervorgänge mit demselben Namen und derselben Zielkategorie aktualisieren die bereits angelegte Designkopie.

### 6. Hinweise

- Das Modul verändert das Design, jedoch keine Seitenstruktur, Positionen, Navigation, Aktionen oder Objektzuordnungen.
- Für bestehende Views ist **Bestehende Einstellung beibehalten** bei Effekten, Typografie und Formensprache die sichere Ausgangseinstellung.
- Die angebotenen IPSView-Schriften werden in der Vorschau offline dargestellt. Nur zusätzlich erkannte Systemschriften können durch eine lokale Ersatzschrift angezeigt werden.
