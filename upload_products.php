<?php
include 'header.php';
$page_title = 'Import Products';


// Check if user is logged in and if the user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    // Redirect non-admin users to the home page
    header('Location: home.php');
    exit();
}
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($file, 'r')) !== false) {
        // Ler a primeira linha (cabeçalho)
        $header = fgetcsv($handle, 1000, ';');

        // Verifica se os campos obrigatórios existem no CSV
        $requiredFields = ['name', 'reference', 'description', 'price', 'pricevat', 'stock', 'category_id'];
        $missingFields = array_diff($requiredFields, $header);

        if (!empty($missingFields)) {
            $message = 'Os seguintes campos obrigatórios estão ausentes no CSV: ' . implode(';', $missingFields);
        } else {
            // Mapear os índices dos campos
            $fieldIndices = array_flip($header);

            // Inserir cada linha na tabela products
            $insertedCount = 0;
            $invalidRows = 0;

            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                // Verifica se o número de colunas corresponde ao esperado
                if (count($data) < count($header)) {
                    $invalidRows++;
                    continue;
                }

                // Sanitiza os dados
                $name = $mysqli->real_escape_string($data[$fieldIndices['name']]);
                $reference = $mysqli->real_escape_string($data[$fieldIndices['reference']]);
                $description = $mysqli->real_escape_string($data[$fieldIndices['description']]);
                $price = isset($data[$fieldIndices['price']]) ? (float)$data[$fieldIndices['price']] : 0.0;
                $pricevat = isset($data[$fieldIndices['pricevat']]) ? (float)$data[$fieldIndices['pricevat']] : 0.0;
                $stock = isset($data[$fieldIndices['stock']]) ? (int)$data[$fieldIndices['stock']] : 0;
                $category_id = isset($data[$fieldIndices['category_id']]) ? (int)$data[$fieldIndices['category_id']] : null;

                // Verifica se o category_id existe na tabela categories
                if ($category_id !== null) {
                    $stmtCategory = $mysqli->prepare("SELECT id FROM categories WHERE id = ?");
                    $stmtCategory->bind_param('i', $category_id);
                    $stmtCategory->execute();
                    $stmtCategory->store_result();

                    if ($stmtCategory->num_rows === 0) {
                        $stmtCategory->close();
                        $invalidRows++;
                        continue;
                    }
                    $stmtCategory->close();
                } else {
                    $invalidRows++;
                    continue;
                }

                // Inserir o produto
                $stmt = $mysqli->prepare("INSERT INTO products (name, reference, description, price, pricevat, stock, category_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param('sssddii', $name, $reference, $description, $price, $pricevat, $stock, $category_id);

                if ($stmt->execute()) {
                    $insertedCount++;
                } else {
                    $invalidRows++;
                }
                $stmt->close();
            }

            fclose($handle);

            $message = "$insertedCount produtos foram importados com sucesso.";
            if ($invalidRows > 0) {
                $message .= " $invalidRows linhas foram ignoradas devido a erros.";
            }
        }
    } else {
        $message = 'Erro ao abrir o ficheiro CSV.';
    }
}


$mysqli->close();
include 'template.php';
?>

<div class="col-md-10">
    <h1 class="mb-4">Upload Products (CSV)</h1>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    <form action="upload_products.php" method="POST" enctype="multipart/form-data" class="mb-4">
        <div class="mb-3">
            <label for="csv_file" class="form-label">Select CSV File:</label>
            <input type="file" id="csv_file" name="csv_file" class="form-control" accept=".csv" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Import Products</button>
        </div>
    </form>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">CSV Format</h5>
            <pre class="bg-light p-3">
name;reference;description;price;pricevat;stock;category_id
"Product 1";"REF001";"Description 1";100.00;123.00;50;1
"Product 2";"REF002";"Description 2";200.00;246.00;30;2
            </pre>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>