<?php
/**
 * Lade Standort-Daten aus JSON-Datei
 */
function getLocationData($roomId) {
    $locationsFile = __DIR__ . '/../data/locations.json';
    
    if (!file_exists($locationsFile)) {
        return null;
    }
    
    $locationsJson = file_get_contents($locationsFile);
    $locations = json_decode($locationsJson, true);
    
    if (isset($locations[$roomId])) {
        return $locations[$roomId];
    }
    
    return null;
}

/**
 * Formatiere Standort für Anzeige (Stadt, Bezirk, Straße)
 */
function formatLocation($locationData) {
    if (!$locationData) {
        return 'Unbekannter Standort';
    }
    
    // Format: Stadt, Bezirk, Straße (vollständig)
    $parts = [];
    if (isset($locationData['city'])) {
        $parts[] = $locationData['city'];
    }
    if (isset($locationData['district'])) {
        $parts[] = $locationData['district'];
    }
    if (isset($locationData['address'])) {
        // Extrahiere nur Straße aus "Mitte, Friedrichstraße 123"
        $addressParts = explode(', ', $locationData['address']);
        if (count($addressParts) > 1) {
            $parts[] = $addressParts[1]; // Straße
        }
    }
    
    return implode(', ', $parts);
}

/**
 * Formatiere Standort kurz (nur Stadt / Bezirk)
 */
function formatLocationShort($locationData) {
    if (!$locationData) {
        return 'Unbekannter Standort';
    }
    
    // Format: Stadt / Bezirk
    if (isset($locationData['city']) && isset($locationData['district'])) {
        return $locationData['city'] . ' / ' . $locationData['district'];
    }
    
    return $locationData['city'] ?? 'Unbekannt';
}

/**
 * Erstelle Google Maps Link
 */
function getMapLink($locationData) {
    if (!$locationData || !isset($locationData['lat']) || !isset($locationData['lon'])) {
        return '#';
    }
    
    return 'https://www.google.com/maps?q=' . $locationData['lat'] . ',' . $locationData['lon'];
}
?>
