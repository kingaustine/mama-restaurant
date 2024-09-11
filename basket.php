<?php 
session_start();
require "admin/includes/functions.php";
require "admin/includes/db.php";
error_reporting(0);

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//       Section 1 (if user attempts to add something to the cart from the product page)
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if (isset($_GET['fid']) && isset($_GET['qty'])) {
    $fid = $_GET['fid'];
    $qty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
    $wasFound = false;
    $i = 0;

    // If the cart session variable is not set or cart array is empty
    if (!isset($_SESSION["cart_array"]) || count($_SESSION["cart_array"]) < 1) { 
        // RUN IF THE CART IS EMPTY OR NOT SET
        $_SESSION["cart_array"] = array(0 => array("item_id" => $fid, "quantity" => $qty));
    } else {
        // RUN IF THE CART HAS AT LEAST ONE ITEM IN IT
        foreach ($_SESSION["cart_array"] as $each_item) { 
            $i++;
            while (list($key, $value) = each($each_item)) {
                if ($key == "item_id" && $value == $fid) {
                    // That item is in cart already so let's adjust its quantity using array_splice()
                    array_splice($_SESSION["cart_array"], $i-1, 1, array(array("item_id" => $fid, "quantity" => $each_item['quantity'] + $qty)));
                    $wasFound = true;
                } 
            }
        }
        if ($wasFound == false) {
            array_push($_SESSION["cart_array"], array("item_id" => $fid, "quantity" => $qty));
        }
    }
    header("location: basket.php"); 
    exit();
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//       Section 2 (if user chooses to empty their shopping cart)
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if (isset($_GET['cmd']) && $_GET['cmd'] == "emptycart") {
    unset($_SESSION["cart_array"]);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//       Section 4 (if user wants to remove an item from cart)
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if (isset($_POST['index_to_remove']) && $_POST['index_to_remove'] != "") {
    // Access the array and run code to remove that array index
    $key_to_remove = $_POST['index_to_remove'];
    if (count($_SESSION["cart_array"]) <= 1) {
        unset($_SESSION["cart_array"]);
    } else {
        unset($_SESSION["cart_array"]["$key_to_remove"]);
        sort($_SESSION["cart_array"]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="" />
<meta name="keywords" content="" />
<head>
    <title>Mama Restaurant</title>
    <link rel="stylesheet" href="css/main.css" />
    <script src="js/jquery.min.js"></script>
    <script src="js/myscript.js"></script>
</head>
<body>
    <?php require "includes/header.php"; ?>
    <br/><br/><br/>
    <div class="content remove_pad" onclick="remove_class()">
        <div class="inner_content on_parallax">
            <h2><span class="fresh">Your Order</span></h2>
            <div class="parallax_content">
                <?php
                $cartOutput = "";
                $cartTotal = "";
                if (!isset($_SESSION["cart_array"]) || count($_SESSION["cart_array"]) < 1) {
                    $cartOutput = "<h3 align='center'>Your shopping cart is empty</h3>";
                } else {
                    $i = 0;
                    foreach ($_SESSION["cart_array"] as $each_item) {
                        $item_id = $each_item['item_id'];
                        $sql = $db->query("SELECT * FROM food WHERE id='$item_id' LIMIT 1");
                        while ($row = $sql->fetch_assoc()) {
                            $food_name = $row["food_name"];
                            $price = $row["food_price"];
                            $details = $row["food_description"];
                        }
                        $pricetotal = $price * $each_item['quantity'];
                        $cartTotal += $pricetotal;

                        $cartOutput .= "<tr>";
                        $cartOutput .= "<td>$food_name</td>";
                        $cartOutput .= "<td>$details</td>";
                        $cartOutput .= "<td>Ugx $price</td>";
                        $cartOutput .= "<td><form action='basket.php' method='post'>
                                        <input name='quantity' type='text' value='".$each_item['quantity']."' size='1' maxlength='2'/>
                                        <input name='adjustBtn".$item_id."' type='submit' value='change' /></form></td>";
                        $cartOutput .= "<td>Ugx ".$pricetotal."</td>";
                        $cartOutput .= "<td><form action='basket.php' method='post'>
                                        <input name='deleteBtn".$item_id."' type='submit' value='X' />
                                        <input name='index_to_remove' type='hidden' value='".$i."' /></form></td>";
                        $cartOutput .= "</tr>";
                        $i++; 
                    }
                    $cartTotal = "<div class='cart_total_price'><h3>Total Price : Ugx $cartTotal</h3></div>";
                }
                ?>

                <div class="cart_holder">
                    <table class="cart_table" width="100%" cellpadding="6" cellspacing="0">
                        <tr>
                            <th>Product Name</th>
                            <th>Product Description</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Remove</th>
                        </tr>
                        <?php echo $cartOutput; ?>
                    </table>
                    <?php echo $cartTotal; ?>
                    <br>
                    <div class="cart_buttons">
                        <form action="basket.php" method="get">
                            <input name="cmd" type="hidden" value="emptycart" />
                            <button type="submit" class="empty_cart_btn">Empty Cart</button>
                        </form>
                        <a href="checkout.php" class="checkout_btn">Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<script>
function remove_class() {
    // Your JavaScript code here
}
</script>
