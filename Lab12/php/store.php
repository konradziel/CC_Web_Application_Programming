<?php
class Store {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
            $_SESSION['cart_count'] = 0;
        }
        $this->handleCartActions();
    }

    private function addToCart($product_id, $quantity) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        $product = $this->getProductDetails($product_id);
        if ($product) {
            $_SESSION['cart'][$product_id] = $quantity;
            $this->updateCartCount();
            return true;
        }
        return false;
    }

    public function handleCartActions() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'add':
                        if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
                            $product_id = (int)$_POST['product_id'];
                            $quantity = (int)$_POST['quantity'];
                            if ($quantity > 0) {
                                $this->addToCart($product_id, $quantity);
                            }
                        }
                        break;

                    case 'update':
                        if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
                            $product_id = (int)$_POST['product_id'];
                            $quantity = (int)$_POST['quantity'];
                            $this->updateCartQuantity($product_id, $quantity);
                        }
                        break;

                    case 'remove':
                        if (isset($_POST['product_id'])) {
                            $product_id = (int)$_POST['product_id'];
                            $this->removeFromCart($product_id);
                        }
                        break;

                    case 'place_order':
                        // Redirect to success page after order placement
                        header('Location: ?idp=sklep&page=success');
                        exit;
                        break;
                }
            }
        }
    }

    private function updateCartQuantity($product_id, $quantity) {
        if (isset($_SESSION['cart'][$product_id])) {
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id] = $quantity;
            } else {
                $this->removeFromCart($product_id);
            }
            $this->updateCartCount();
        }
    }

    private function removeFromCart($product_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            $this->updateCartCount();
        }
    }

    private function updateCartCount() {
        $_SESSION['cart_count'] = array_sum($_SESSION['cart']);
    }

    public function __toString() {
        $output = '';
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        $page = isset($_GET['page']) ? $_GET['page'] : '';

        // Start store container
        $output .= '<div class="store-container">';
        $output .= '<button class="menu-toggle">☰</button>';
        $output .= $this->showSidebar();
        $output .= '<div class="store-content">';
        
        // Always show store header except for cart and checkout pages
        if (!in_array($page, ['cart', 'checkout', 'success'])) {
            $output .= '<h1 class="store-header">Sklep</h1>';
        }

        // Show different content based on action/page
        if ($page === 'cart') {
            $output .= $this->showCartContent();
        } elseif ($page === 'checkout') {
            $output .= $this->showCheckoutForm();
        } elseif ($page === 'success') {
            $output .= $this->showSuccessPage();
        } elseif ($action === 'product') {
            $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($productId) {
                $output .= $this->showProductDetails($productId);
            }
        } elseif ($action === 'category') {
            $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
            if ($categoryId) {
                $output .= $this->showCategoryProducts($categoryId);
            }
        } else {
            $output .= $this->showAllProducts();
        }

        $output .= '</div></div>';
        return $output;
    }

    public function showCart() {
        $output = '<div class="store-container">';
        $output .= '<button class="menu-toggle">☰</button>';
        $output .= $this->showSidebar();
        $output .= '<div class="store-content">';
        $output .= $this->showCartContent();
        $output .= '</div></div>';
        return $output;
    }

    private function showCartContent() {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            return '<div class="cart-empty">Koszyk jest pusty</div>';
        }

        $output = '<div class="cart-content">';
        $output .= '<h2>Koszyk</h2>';
        $total = 0;

        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $product = $this->getProductDetails($productId);
            if ($product) {
                $price_net = $product['cena_netto'];
                $vat = $product['podatek_vat'];
                $price_gross = $price_net * (1 + ($vat / 100));
                $subtotal = $price_gross * $quantity;
                $total += $subtotal;

                $output .= sprintf('
                    <div class="cart-item">
                        <img src="%s" alt="%s" class="cart-item-image">
                        <div class="cart-item-details">
                            <h3>%s</h3>
                            <p class="cart-item-price">Cena: %.2f zł</p>
                            <div class="cart-item-quantity">
                                <form method="post" class="update-quantity-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="%d">
                                    <input type="number" name="quantity" value="%d" min="1" max="%d" class="quantity-input">
                                    <button type="submit" class="update-btn">Aktualizuj</button>
                                </form>
                                <form method="post" class="remove-item-form">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="%d">
                                    <button type="submit" class="remove-btn">Usuń</button>
                                </form>
                            </div>
                            <p class="cart-item-subtotal">Suma: %.2f zł</p>
                        </div>
                    </div>
                ',
                    htmlspecialchars($product['zdjecie'] ?? 'default.jpg'),
                    htmlspecialchars($product['tytul']),
                    htmlspecialchars($product['tytul']),
                    $price_gross,
                    $product['id'],
                    $quantity,
                    $product['ilosc_magazyn'],
                    $product['id'],
                    $subtotal
                );
            }
        }

        $output .= sprintf('
            <div class="cart-summary">
                <p class="cart-total">Suma całkowita: %.2f zł</p>
                <a href="?idp=sklep&page=checkout" class="checkout-btn">Przejdź do kasy</a>
            </div>
        ', $total);

        $output .= '</div>';
        return $output;
    }

    private function showCheckoutForm() {
        $total = 0;
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $product = $this->getProductDetails($productId);
            if ($product) {
                $price_gross = $product['cena_netto'] * (1 + ($product['podatek_vat'] / 100));
                $total += $price_gross * $quantity;
            }
        }

        return sprintf('
            <div class="checkout-container">
                <h2>Dane do zamówienia</h2>
                <form method="post" class="checkout-form">
                    <input type="hidden" name="action" value="place_order">
                    <div class="form-group">
                        <label for="name">Imię i nazwisko:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Telefon:</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Adres dostawy:</label>
                        <textarea id="address" name="address" required></textarea>
                    </div>
                    <div class="order-summary">
                        <p class="total">Suma do zapłaty: %.2f zł</p>
                    </div>
                    <button type="submit" class="purchase-btn">Kup teraz</button>
                </form>
            </div>
        ', $total);
    }

    private function showSuccessPage() {
        // Clear the cart after successful purchase
        $_SESSION['cart'] = array();
        $this->updateCartCount();

        return '
            <div class="success-container">
                <h2>Zakup udany!</h2>
                <p>Dziękujemy za złożenie zamówienia.</p>
                <a href="?idp=sklep" class="return-to-shop">Wróć do sklepu</a>
            </div>
        ';
    }

    private function getCategoryTree($parentId = 0) {
        $query = "SELECT id, nazwa FROM category_list WHERE matka = ? ORDER BY nazwa";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $parentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = array();
        while ($row = $result->fetch_assoc()) {
            $category = array(
                'id' => $row['id'],
                'name' => $row['nazwa'],
                'children' => $this->getCategoryTree($row['id'])
            );
            $categories[] = $category;
        }
        return $categories;
    }

    private function renderCategoryTree($categories, $level = 0) {
        $output = '<ul class="category-tree style="white-space: normal' . ($level === 0 ? ' main-tree' : '') . '">';
        foreach ($categories as $category) {
            $hasChildren = !empty($category['children']);
            $output .= '<li class="category-item' . ($hasChildren ? ' has-children' : ' no-children') . '">';
            $output .= '<div class="category-header level-' . $level . '">';
            $output .= '<span class="toggle-btn">' . ($hasChildren ? '+' : '−') . '</span>';
            $output .= '<a href="?idp=sklep&action=category&category=' . $category['id'] . '">' . htmlspecialchars($category['name']) . '</a>';
            $output .= '</div>';
            if ($hasChildren) {
                $output .= $this->renderCategoryTree($category['children'], $level + 1);
            }
            $output .= '</li>';
        }
        $output .= '</ul>';
        return $output;
    }

    private function showSidebar() {
        $categories = $this->getCategoryTree();
        $output = '
        <div class="store-sidebar">
            <div class="sidebar-header">
                <h3 class="sidebar-title">Kategorie</h3>
                <button class="sidebar-toggle">×</button>
            </div>
            <div class="sidebar-content">';
        
        // Add cart link at the top of sidebar
        $cartCount = isset($_SESSION['cart_count']) ? $_SESSION['cart_count'] : 0;
        $output .= '<div class="sidebar-cart">
            <a href="?idp=sklep&page=cart">
                <span class="cart-icon">🛒</span>
                <span class="cart-text">Koszyk (' . $cartCount . ')</span>
            </a>
        </div>';
        
        $output .= $this->renderCategoryTree($categories);
        $output .= '</div></div>';
        return $output;
    }

    private function getCategoryName($categoryId) {
        $query = "SELECT nazwa FROM category_list WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['nazwa'];
        }
        return 'Brak kategorii';
    }

    private function getProductDetails($product_id) {
        $query = "SELECT * FROM product_list WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    private function renderProductCard($product) {
        $price_net = $product['cena_netto'];
        $vat = $product['podatek_vat'];
        $price_gross = $price_net * (1 + ($vat / 100));
        $availability = $product['status_dostepnosci'];
        $availability_class = $product['status_dostepnosci'] === 'dostępny' ? 'in-stock' : 'out-of-stock';
        $category_name = $this->getCategoryName($product['kategoria']);

        return sprintf('
            <div class="product-card" data-product-id="%d">
                <div class="product-clickable">
                    <img src="%s" alt="%s" class="product-image">
                    <div class="product-info">
                        <h3>%s</h3>
                        <p class="product-description">%s</p>
                        <p class="price">
                            <span class="price-net">Netto: %.2f zł</span><br>
                            <span class="price-gross">Brutto: %.2f zł</span>
                        </p>
                        <p class="vat">VAT: %d%%</p>
                        <p class="availability %s">%s</p>
                    </div>
                </div>
            </div>

            <div id="product-modal-%d" class="modal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <div class="modal-grid">
                        <div class="modal-image">
                            <img src="%s" alt="%s">
                        </div>
                        <div class="modal-info">
                            <h2>%s</h2>
                            <div class="modal-description">%s</div>
                            <div class="modal-meta">
                                <div class="price-info">
                                    <p class="price-net">Cena netto: %.2f zł</p>
                                    <p class="price-gross">Cena brutto: %.2f zł</p>
                                    <p class="vat">VAT: %d%%</p>
                                </div>
                                <p class="availability %s">Status: %s</p>
                                <p class="stock">Ilość w magazynie: %d szt.</p>
                                <p class="category">Kategoria: %s</p>
                                <p class="dimensions">Gabaryt: %s</p>
                            </div>
                            <form method="post" class="add-to-cart-form">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="%d">
                                <div class="quantity-wrapper">
                                    <label for="quantity-%d">Ilość:</label>
                                    <input type="number" id="quantity-%d" name="quantity" value="1" min="1" max="%d" class="quantity-input">
                                </div>
                                <button type="submit" class="add-to-cart-btn" %s>Dodaj do koszyka</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        ',
            $product['id'],
            htmlspecialchars($product['zdjecie'] ?? 'default.jpg'),
            htmlspecialchars($product['tytul']),
            htmlspecialchars($product['tytul']),
            htmlspecialchars(substr($product['opis'], 0, 100) . '...'),
            $price_net,
            $price_gross,
            $vat,
            $availability_class,
            ucfirst($availability),
            // Modal parameters
            $product['id'],
            htmlspecialchars($product['zdjecie'] ?? 'default.jpg'),
            htmlspecialchars($product['tytul']),
            htmlspecialchars($product['tytul']),
            nl2br(htmlspecialchars($product['opis'])),
            $price_net,
            $price_gross,
            $vat,
            $availability_class,
            ucfirst($availability),
            $product['ilosc_magazyn'],
            htmlspecialchars($category_name),
            htmlspecialchars($product['gabaryt']),
            $product['id'],
            $product['id'],
            $product['id'],
            $product['ilosc_magazyn'],
            $product['status_dostepnosci'] !== 'dostępny' ? 'disabled' : ''
        );
    }

    private function getAllSubcategories($categoryId) {
        $subcategories = array($categoryId);  // Include the parent category
        
        $query = "WITH RECURSIVE subcategories AS (
            SELECT id, matka, nazwa
            FROM category_list
            WHERE matka = ?
            UNION ALL
            SELECT c.id, c.matka, c.nazwa
            FROM category_list c
            INNER JOIN subcategories s ON c.matka = s.id
        )
        SELECT id FROM subcategories";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $subcategories[] = $row['id'];
        }
        
        return $subcategories;
    }

    private function showCategoryProducts($categoryId) {
        $output = '<div class="products-grid">';
        
        // Get all subcategories including the current category
        $subcategories = $this->getAllSubcategories($categoryId);
        $subcategoriesStr = implode(',', array_map('intval', $subcategories));
        
        // Get products from current category and all subcategories
        $query = "SELECT * FROM product_list WHERE kategoria IN ($subcategoriesStr) AND status_dostepnosci = 'dostępny'";
        $result = $this->conn->query($query);

        if (!$result || $result->num_rows === 0) {
            $output .= '<div class="no-products">Brak produktów w tej kategorii</div>';
        } else {
            while ($product = $result->fetch_assoc()) {
                $output .= $this->renderProductCard($product);
            }
        }

        $output .= '</div>';
        return $output;
    }

    private function showAllProducts() {
        $output = '<div class="products-grid">';
        
        // Get all products from product_list table
        $query = "SELECT * FROM product_list WHERE status_dostepnosci = 'dostępny'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $output .= '<div class="no-products">Brak dostępnych produktów</div>';
        } else {
            while ($product = $result->fetch_assoc()) {
                $output .= $this->renderProductCard($product);
            }
        }

        $output .= '</div>';
        return $output;
    }

    private function showProductDetails($productId) {
        $query = "SELECT * FROM product_list WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        if (!$product) {
            return '<div class="error-message">Produkt nie został znaleziony.</div>';
        }

        $price_net = $product['cena_netto'];
        $vat = $product['podatek_vat'];
        $price_gross = $price_net * (1 + ($vat / 100));

        $output = '<div class="product-details">';
        $output .= '<div class="product-details-header">';
        $output .= '<a href="?idp=sklep" class="back-button">← Powrót do sklepu</a>';
        $output .= sprintf('<h1>%s</h1>', htmlspecialchars($product['tytul']));
        $output .= '</div>';

        $output .= '<div class="product-details-content">';
        $output .= '<div class="product-details-image">';
        $output .= sprintf('<img src="%s" alt="%s">', 
            htmlspecialchars($product['zdjecie'] ?? 'default.jpg'),
            htmlspecialchars($product['tytul'])
        );
        $output .= '</div>';

        $output .= '<div class="product-details-info">';
        $output .= '<div class="product-details-description">';
        $output .= sprintf('<p>%s</p>', nl2br(htmlspecialchars($product['opis'])));
        $output .= '</div>';

        $output .= '<div class="product-details-meta">';
        $output .= sprintf('
            <div class="price-info">
                <p class="price-net">Cena netto: %.2f zł</p>
                <p class="price-gross">Cena brutto: %.2f zł</p>
                <p class="vat">VAT: %d%%</p>
            </div>
            <p class="availability %s">Status: %s</p>
            <p class="stock">Ilość w magazynie: %d szt.</p>
            <p class="category">Kategoria: %s</p>
            <p class="dimensions">Gabaryt: %s</p>
            ',
            $price_net,
            $price_gross,
            $vat,
            $product['status_dostepnosci'] === 'dostępny' ? 'in-stock' : 'out-of-stock',
            ucfirst($product['status_dostepnosci']),
            $product['ilosc_magazyn'],
            htmlspecialchars($this->getCategoryName($product['kategoria'])),
            htmlspecialchars($product['gabaryt'])
        );

        if ($product['status_dostepnosci'] === 'dostępny') {
            $output .= '<form method="post" class="add-to-cart-form">';
            $output .= '<input type="hidden" name="action" value="add">';
            $output .= sprintf('<input type="hidden" name="product_id" value="%d">', $product['id']);
            $output .= sprintf('
                <div class="quantity-wrapper">
                    <label for="quantity">Ilość:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="%d" class="quantity-input">
                </div>
                <button type="submit" class="add-to-cart-btn">Dodaj do koszyka</button>
                ', $product['ilosc_magazyn']
            );
            $output .= '</form>';
        }

        $output .= '</div>'; // End product-details-meta
        $output .= '</div>'; // End product-details-info
        $output .= '</div>'; // End product-details-content
        $output .= '</div>'; // End product-details

        return $output;
    }
}
?>
