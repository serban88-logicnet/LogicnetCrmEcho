<?php
$apiKey = 'Q-KHW8yYtv7pvwxxZtWxzkv9RzgyshJNduS71dtKeEPj4_7ACA';
$rezultat = null;
$eroare = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cui'])) {
    $cui = preg_replace('/\D/', '', $_POST['cui']);
    $url = "https://api.openapi.ro/api/companies/$cui";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $apiKey,
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        $eroare = "Eroare CURL: $curlError";
    } elseif ($httpCode === 200) {
        $rezultat = json_decode($response, true);
        echo "<pre>"; print_r($rezultat); echo "</pre>";
    } elseif ($httpCode === 202) {
        $eroare = "Firma nu e în baza de date încă, dar a fost pusă în coadă. Revino mai târziu.";
    } elseif ($httpCode === 404) {
        $eroare = "Firma nu a fost găsită sau CUI invalid.";
    } elseif ($httpCode === 403) {
        $eroare = "Cheia API este invalidă.";
    } else {
        $eroare = "Eroare necunoscută. Cod HTTP: $httpCode";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Găsește firmă după CUI</title>
</head>
<body>
    <h2>Caută firmă după CUI</h2>
    <form method="post">
        <label for="cui">CUI:</label>
        <input type="text" name="cui" id="cui" required>
        <button type="submit">Caută</button>
    </form>

    <?php if ($rezultat): ?>
        <h3>Rezultate:</h3>
        <ul>
            <li><strong>Denumire:</strong> <?= htmlspecialchars($rezultat['denumire']) ?></li>
            <li><strong>CUI:</strong> <?= htmlspecialchars($rezultat['cif']) ?></li>
            <li><strong>Adresă:</strong> <?= htmlspecialchars($rezultat['adresa']) ?></li>
            <li><strong>Județ:</strong> <?= htmlspecialchars($rezultat['judet']) ?></li>
            <li><strong>Stare:</strong> <?= htmlspecialchars($rezultat['stare']) ?></li>
            <li><strong>TVA din:</strong> <?= htmlspecialchars($rezultat['tva'] ?? '—') ?></li>
        </ul>
    <?php elseif ($eroare): ?>
        <p style="color:red;"><strong><?= htmlspecialchars($eroare) ?></strong></p>
    <?php endif; ?>
</body>
</html>
