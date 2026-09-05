# IPSView Assistant

[![Symcon](https://img.shields.io/badge/Symcon-PHPModul-555555.svg)](https://www.symcon.de)
[![Modul Version](https://img.shields.io/badge/Modul%20Version-1.92-blue.svg)](library.json)
[![Symcon Version](https://img.shields.io/badge/Symcon%20Version-9.1%2B-brightgreen.svg)](https://www.symcon.de)<br>
[![License](https://img.shields.io/badge/License-PolyForm--Noncommercial--1.0.0-brightgreen.svg)](LICENSE)
[![Check Style](https://github.com/Burki24/IPSViewAssistant/actions/workflows/style.yml/badge.svg?branch=main)](https://github.com/Burki24/IPSViewAssistant/actions/workflows/style.yml?query=branch%3Amain)
[![Run Tests](https://github.com/Burki24/IPSViewAssistant/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/Burki24/IPSViewAssistant/actions/workflows/tests.yml?query=branch%3Amain)

IPSView Assistant erleichtert das Erstellen und Gestalten von IPSView-Projekten direkt in Symcon.

Ein nativer vierseitiger Schnellstart-Wizard führt mit Einsatzprofilen für Wandtablet, Tablet, Smartphone und Browser, einem optionalen zwei- oder dreispaltigen Start-Raster und einem wählbaren Design durch die grundlegenden Einstellungen. Ein Startcheck prüft die Konfiguration vor der Erstellung und erlaubt das bewusste Überschreiben einer eindeutig erkannten gleichnamigen IPSView; im Expertenmodus können bestehende Views als separate Designkopie neu gestaltet werden. Das Original bleibt unverändert.

Im Expertenmodus können Designs außerdem als portables **Style Profile V1** exportiert und wieder importiert werden. Der Assistant unterstützt vollständiges JSON sowie wiederverwendbare Symcon-Dokumentmedien. Ein importiertes Profil befüllt den Designeditor und kann anschließend angepasst und erneut exportiert werden.

Zusätzlich bildet der gemeinsame IPSView-Stileditor die **109 nativen IPSView-Farbfelder in 15 Gruppen** ab. Die nativen Farben erben standardmäßig aus den portablen semantischen Designrollen; einzelne Felder können im benutzerdefinierten Stil gezielt als **Abweichend** überschrieben werden. Eine manuelle Farbänderung aktiviert die betreffende Abweichung automatisch. Die Live-Vorschau verwendet die aufgelösten nativen Farben, wobei `ColorView` und `ColorPage` bewusst getrennte View- und Seitenhintergründe bleiben.

Folgende Module enthält die Bibliothek:

- **IPSView Assistant** ([Dokumentation](IPSView%20Assistant/README.md)) – Erstellt neue IPSView-Projekte oder gestaltet bestehende Views über eine sichere Kopie.

## Voraussetzungen

- Symcon ab Version 9.1
- Installiertes und lizenziertes IPSView (Standard oder Professional)
- IPSView Designer zum Öffnen, Bearbeiten und Speichern der erzeugten View; ohne erkannten Designer bleibt die Erstellung mit einem gelben Startcheck-Hinweis möglich
