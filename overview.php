<?php 
session_start();
require "includes/functions.php";
require "includes/db.php";

if (!isset($_SESSION['user'])) {
    header("location: logout.php");
    exit();
}

$result = "";
$info = "";
$items = "";
$pagenum = "";
$per_page = 10;

$count = $db->query("SELECT * FROM basket");
$pages = ceil((mysqli_num_rows($count)) / $per_page);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

$orders = $db->prepare("SELECT * FROM basket LIMIT ?, ?");
$orders->bind_param("ii", $start, $per_page);
$orders->execute();
$result_orders = $orders->get_result();

if ($result_orders->num_rows) {
    $x = 1;
    $info .= "<table class='table table-hover'>
                <thead>
                    <th>Order_id</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Email</th>
                    <th>Phone</th>
                </thead>
                <tbody>";
    $items .= "<table class='table table-hover'>
                <tbody>
                <tr>
                    <th>Name</th>
                    <th>Qty</th>
                    <td></td>
                </tr>";
    
    while ($row = $result_orders->fetch_assoc()) {
        $oid = htmlspecialchars($row['id']);
        $id = htmlspecialchars($row['id']) . "_ord";

        if ($x == 1) {
            $result .=  "<input type='hidden' value='" . htmlspecialchars($id) . "' id='" . htmlspecialchars($id) . "'>
                        <a href='#' style='display: block; background: #efefef; color: #333; border-bottom: 1px solid #ccc; padding: 10px 0px;' 
                        onClick=\"func_call('" . htmlspecialchars($id) . "'); return false\" >ORD_" . htmlspecialchars($oid) . "</a>";

            $info .= "<tr>
                        <td>ORD_" . htmlspecialchars($oid) . "</td>
                        <td>" . htmlspecialchars($row['customer_name']) . "</td>
                        <td>" . htmlspecialchars($row['address']) . "</td>
                        <td>" . htmlspecialchars($row['email']) . "</td>
                        <td>" . htmlspecialchars($row['contact_number']) . "</td>
                    </tr>";

            $get_data = $db->prepare("SELECT * FROM items WHERE order_id=?");
            $get_data->bind_param("i", $oid);
            $get_data->execute();
            $result_data = $get_data->get_result();

            while ($data = $result_data->fetch_assoc()) {
                $items .= "<tr>
                            <td>" . htmlspecialchars($data['food']) . "</td>
                            <td>" . htmlspecialchars($data['qty']) . "</td>
                            <td></td>
                        </tr>";
            }

            $items .= "<tr>
                        <th>Total Price</th>
                        <th>" . htmlspecialchars($row['total']) . "</th>
                        <th></th>
                    </tr>";

            $statusOptions = "";
            if ($row['status'] == "pending") {
                $statusOptions = "<option value='pending_$oid' selected>pending</option>
                                  <option value='confirmed_$oid'>confirmed</option>";
            } else {
                $statusOptions = "<option value='pending_$oid'>pending</option>
                                  <option value='confirmed_$oid' selected>confirmed</option>";
            }

            $items .= "<tr>
                        <th>Status</th>
                        <td>
                            <select onChange=\"change_stat('" . htmlspecialchars($oid) . "')\" name='status' id='" . htmlspecialchars($oid) . "' class='form-control'>
                                $statusOptions
                            </select>
                        </td>
                        <th></th>
                    </tr>";
        } else {
            $result .=  "<input type='hidden' value='" . htmlspecialchars($id) . "' id='" . htmlspecialchars($id) . "'>
                        <a href='#' style='display: block; background: #efefef; color: #333; border-bottom: 1px solid #ccc; padding: 10px 0px;' 
                        onClick=\"func_call('" . htmlspecialchars($id) . "'); return false\" >ORD_" . htmlspecialchars($oid) . "</a>";
        }
        $x++;
    }

    $info .= "</tbody>
            </table>";
    $items .= "</tbody>
            </table>";
} else {
    $result = "No Orders available yet";
    $info = "";
    $items = "";
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['delete']) && !empty($_GET['delete'])) {
        $bird_id = (int) escape($_GET['delete']);
        if ($bird_id != "") {
            $query = $db->prepare("DELETE FROM birds WHERE bird_id=? LIMIT 1");
            $query->bind_param('i', $bird_id);
            if ($query->execute()) {
                echo "<script>alert('Record deleted successfully')</script>";
            } else {
                echo "<script>alert('Record was not deleted successfully')</script>";
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <link rel="icon" type="image/png" href="assets/img/favicon.ico">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Mama Restaurant</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Animation library for notifications -->
    <link href="assets/css/animate.min.css" rel="stylesheet"/>
    <!--  Light Bootstrap Table core CSS    -->
    <link href="assets/css/light-bootstrap-dashboard.css?v=1.4.0" rel="stylesheet"/>
    <!--  CSS for Demo Purpose, don't include it in your project     -->
    <link href="assets/css/demo.css" rel="stylesheet" />

    <!--     Fonts and icons     -->
    <link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Roboto:400,700,300' rel='stylesheet' type='text/css'>
    <link href="assets/css/pe-icon-7-stroke.css" rel="stylesheet" />
</head>
<body>

<div class="wrapper">
    <div class="sidebar" data-color="purple" data-image="assets/img/sidebar-5.jpg">

    <!-- Sidebar here -->

    </div>

    <div class="main-panel">
        <nav class="navbar navbar-default navbar-fixed">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navigation-example-2">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">Orders</a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-left">
                        <!-- Navbar content here -->
                    </ul>

                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <a href="logout.php">
                                Log out
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>


        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="header">
                                <h4 class="title">Orders</h4>
                                <p class="category">Click on an order to view details</p>
                            </div>
                            <div class="content">
                                <?php echo $result; ?>
                                <div class="footer">
                                    <div class="stats">
                                        <?php
                                            for($x = 1; $x <= $pages; $x++) {
                                                echo "<a href='?page=$x' class='btn btn-primary'>$x</a> ";
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="header">
                                <h4 class="title">Order Information</h4>
                                <p class="category">Details of the selected order</p>
                            </div>
                            <div class="content">
                                <?php echo $info; ?>
                            </div>
                        </div>

                        <div class="card">
                            <div class="header">
                                <h4 class="title">Order Items</h4>
                                <p class="category">Items in the selected order</p>
                            </div>
                            <div class="content">
                                <?php echo $items; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="container-fluid">
                <p class="copyright pull-right">
                    &copy; <?php echo date("Y"); ?> <a href="#">Mama Restaurant</a>, made with love for a better web
                </p>
            </div>
        </footer>

    </div>
</div>

<!-- JavaScript files -->
<script src="assets/js/jquery.3.2.1.min.js" type="text/javascript"></script>
<script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
<script src="assets/js/bootstrap-notify.js"></script>
<script src="assets/js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script src="assets/js/demo.js"></script>

<script>
function func_call(id) {
    // Function to display order details when clicked
    // Implement this based on your requirements
}

function change_stat(oid) {
    // Function to change order status
    var status = document.getElementById(oid).value;
    window.location.href = "change_status.php?order_id=" + oid + "&status=" + status;
}
</script>

</body>
</html>
