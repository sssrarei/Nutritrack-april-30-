<?php
include '../includes/auth.php';
include '../config/database.php';

if($_SESSION['role_id'] != 2){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";

// Kunin lahat ng assigned CDC ng logged-in CDW
$cdc_result = $conn->query("
    SELECT c.cdc_id, c.cdc_name, c.barangay
    FROM cdw_assignments ca
    JOIN cdc c ON ca.cdc_id = c.cdc_id
    WHERE ca.user_id = '$user_id'
    ORDER BY c.cdc_name ASC
");

// Switch active CDC
if(isset($_POST['switch_cdc'])){
    $selected_cdc_id = $_POST['cdc_id'];

    $check = $conn->query("
        SELECT * FROM cdw_assignments
        WHERE user_id = '$user_id' AND cdc_id = '$selected_cdc_id'
    ");

    if($check && $check->num_rows > 0){
        $_SESSION['active_cdc_id'] = $selected_cdc_id;

        $cdc_info = $conn->query("
            SELECT cdc_name, barangay
            FROM cdc
            WHERE cdc_id = '$selected_cdc_id'
        ");

        if($cdc_info && $cdc_info->num_rows > 0){
            $cdc_row = $cdc_info->fetch_assoc();
            $_SESSION['active_cdc_name'] = $cdc_row['cdc_name'];
            $_SESSION['active_cdc_barangay'] = $cdc_row['barangay'];
        }

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid CDC selection.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>CDW Dashboard | NutriTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
     <link rel="stylesheet" href="../assets/cdw-style.css">
    <style>
        *{
            box-sizing:border-box;
            margin:0;
            padding:0;
        }

        body{
            background:#eef0f3;
            font-family:'Inter', sans-serif;
            color:#333;
        }

        a{
            text-decoration:none;
        }

       
       
        

        /* MAIN CONTENT */
        .main-content{
            margin-left:260px;
            padding:112px 24px 30px;
            transition:margin-left 0.25s ease;
        }

        .main-content.full{
            margin-left:0;
        }

        .page-card{
            background:#ffffff;
            border:1px solid #dcdcdc;
            border-radius:14px;
            padding:24px;
            margin-bottom:18px;
        }

        .error-msg{
            color:#c62828;
            font-size:13px;
            margin-bottom:14px;
            text-align:center;
        }

        .cdc-switch-area{
            text-align:center;
        }

        .cdc-title{
            font-family:'Poppins', sans-serif;
            font-size:24px;
            font-weight:700;
            text-transform:uppercase;
            color:#3a3a3a;
            margin-bottom:14px;
        }

        .cdc-form{
            display:flex;
            justify-content:center;
            align-items:center;
            gap:10px;
            flex-wrap:wrap;
            margin-bottom:10px;
        }

        .cdc-form select{
            min-width:280px;
            padding:10px 12px;
            border:1px solid #cfcfcf;
            border-radius:8px;
            font-family:'Inter', sans-serif;
            font-size:13px;
            background:#fff;
            outline:none;
        }

        .cdc-form button{
            padding:10px 16px;
            border:none;
            border-radius:8px;
            background:#2e7d32;
            color:#fff;
            font-family:'Inter', sans-serif;
            font-size:13px;
            font-weight:600;
            cursor:pointer;
        }

        .active-cdc-text{
            margin-top:4px;
            font-size:12px;
            color:#666;
        }

        .cards-grid{
            display:grid;
            grid-template-columns:repeat(5, 1fr);
            gap:12px;
            margin-top:22px;
        }

        .card{
            border:1.5px solid #b8b8b8;
            background:#fff;
            min-height:112px;
            display:flex;
            flex-direction:column;
            border-radius:8px;
            overflow:hidden;
        }

        .card-title{
            min-height:52px;
            display:flex;
            align-items:center;
            justify-content:center;
            text-align:center;
            padding:8px 10px;
            font-family:'Poppins', sans-serif;
            font-size:16px;
            line-height:1.2;
            border-bottom:1.5px solid #b8b8b8;
        }

        .card-body{
            flex:1;
            background:#fff;
        }

        .title-normal{
            color:#2e7d32;
            background:#dfe8de;
        }

        .title-alert{
            color:#d12c24;
            background:#efdfdc;
        }

        .chart-section{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:20px;
        }

        .chart-box{
            background:#fff;
            border:1.5px solid #d0d0d0;
            border-radius:12px;
            min-height:325px;
            padding:18px 16px 14px;
        }

        .chart-title{
            font-family:'Poppins', sans-serif;
            font-size:16px;
            text-align:center;
            color:#3d3d3d;
            margin-bottom:18px;
        }

        .fake-chart{
            height:255px;
            display:flex;
            align-items:flex-end;
            gap:12px;
            padding:0 10px 0;
            border-bottom:1px solid #dcdcdc;
        }

        .fake-bar{
            flex:1;
            border-radius:4px 4px 0 0;
        }

        .bar-green-1{ height:52%; background:#8acb99; }
        .bar-green-2{ height:68%; background:#5ea96a; }
        .bar-green-3{ height:86%; background:#5ca767; }
        .bar-green-4{ height:48%; background:#8acb99; }
        .bar-green-5{ height:56%; background:#9ac29f; }
        .bar-green-6{ height:52%; background:#5ea96a; }
        .bar-green-7{ height:63%; background:#5ca767; }
        .bar-green-8{ height:60%; background:#a1c7a5; }

        .bar-mix-1{ height:34%; background:#47b248; }
        .bar-mix-2{ height:50%; background:#ddbbbb; }
        .bar-mix-3{ height:63%; background:#ff3d3d; }
        .bar-mix-4{ height:78%; background:#e5b8b8; }
        .bar-mix-5{ height:46%; background:#ff3d3d; }
        .bar-mix-6{ height:54%; background:#dcc0c0; }
        .bar-mix-7{ height:48%; background:#ff3d3d; }
        .bar-mix-8{ height:59%; background:#eb5656; }
        .bar-mix-9{ height:55%; background:#ff3d3d; }

        .chart-labels{
            display:grid;
            grid-template-columns:repeat(8, 1fr);
            gap:12px;
            margin-top:8px;
            font-size:10px;
            color:#555;
            text-align:center;
        }

        .chart-labels.nutri{
            grid-template-columns:repeat(9, 1fr);
        }

        @media (max-width: 1200px){
            .cards-grid{
                grid-template-columns:repeat(3, 1fr);
            }
        }

        @media (max-width: 991px){
            .sidebar{
                transform:translateX(-100%);
            }

            .sidebar.open{
                transform:translateX(0);
            }

            .sidebar-overlay.show{
                display:block;
                position:fixed;
                top:88px;
                left:0;
                width:100%;
                height:calc(100vh - 88px);
                background:rgba(0,0,0,0.25);
                z-index:1040;
            }

            .main-content{
                margin-left:0;
                padding:104px 16px 24px;
            }

            .topbar{
                padding:0 12px;
            }

            .topbar-logo{
                height:44px;
            }

            .user-chip{
                display:none;
            }

            .chart-section{
                grid-template-columns:1fr;
            }

            .cards-grid{
                grid-template-columns:repeat(2, 1fr);
            }
        }

        @media (max-width: 600px){
            .cards-grid{
                grid-template-columns:1fr;
            }

            .cdc-title{
                font-size:20px;
            }

            .chart-box{
                min-height:280px;
            }

            .card-title{
                font-size:14px;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/cdw_topbar.php'; ?>
<?php include '../includes/cdw_sidebar.php'; ?>

<div class="main-content" id="mainContent">

    <div class="page-card">
        <?php if(!empty($error)){ ?>
            <p class="error-msg"><?php echo $error; ?></p>
        <?php } ?>

        <div class="cdc-switch-area">
            <div class="cdc-title">
                <?php
                if(isset($_SESSION['active_cdc_name']) && !empty($_SESSION['active_cdc_name'])){
                    echo htmlspecialchars($_SESSION['active_cdc_name']);
                } else {
                    echo "NO ACTIVE CDC";
                }
                ?>
            </div>

            <form method="POST" class="cdc-form">
                <select name="cdc_id" required>
                    <option value="">Select CDC</option>
                    <?php
                    if($cdc_result && $cdc_result->num_rows > 0){
                        while($cdc = $cdc_result->fetch_assoc()){
                    ?>
                        <option value="<?php echo $cdc['cdc_id']; ?>"
                            <?php
                            if(isset($_SESSION['active_cdc_id']) && $_SESSION['active_cdc_id'] == $cdc['cdc_id']){
                                echo "selected";
                            }
                            ?>>
                            <?php echo htmlspecialchars($cdc['cdc_name'] . " - " . $cdc['barangay']); ?>
                        </option>
                    <?php
                        }
                    }
                    ?>
                </select>
                <button type="submit" name="switch_cdc">Switch</button>
            </form>

            <?php if(isset($_SESSION['active_cdc_name'])){ ?>
                <div class="active-cdc-text">
                    Active CDC:
                    <?php echo htmlspecialchars($_SESSION['active_cdc_name']); ?>
                    <?php
                    if(isset($_SESSION['active_cdc_barangay']) && !empty($_SESSION['active_cdc_barangay'])){
                        echo " - " . htmlspecialchars($_SESSION['active_cdc_barangay']);
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="page-card">
        <div class="cards-grid">
            <div class="card">
                <div class="card-title title-normal">Normal</div>
                <div class="card-body"></div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Underweight</div>
                <div class="card-body"></div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Severely Underweight</div>
                <div class="card-body"></div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Overweight</div>
                <div class="card-body"></div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Obese</div>
                <div class="card-body"></div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Stunted</div>
                <div class="card-body"></div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Severely Stunted</div>
                <div class="card-body"></div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Moderately Wasted</div>
                <div class="card-body"></div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Severely Wasted</div>
                <div class="card-body"></div>
            </div>
        </div>
    </div>

    <div class="chart-section">
        <div class="chart-box">
            <div class="chart-title">
                Summary of Food Group Consumption
                <?php echo isset($_SESSION['active_cdc_name']) ? htmlspecialchars(strtoupper($_SESSION['active_cdc_name'])) : ''; ?>
            </div>

            <div class="fake-chart">
                <div class="fake-bar bar-green-1"></div>
                <div class="fake-bar bar-green-2"></div>
                <div class="fake-bar bar-green-3"></div>
                <div class="fake-bar bar-green-4"></div>
                <div class="fake-bar bar-green-5"></div>
                <div class="fake-bar bar-green-6"></div>
                <div class="fake-bar bar-green-7"></div>
                <div class="fake-bar bar-green-8"></div>
            </div>

            <div class="chart-labels">
                <div>Sugar/Sweets</div>
                <div>Fish/Shellfish/Meat</div>
                <div>Milk Products</div>
                <div>Eggs/Beans</div>
                <div>Vegetables</div>
                <div>Fruits</div>
                <div>Rice/Corn/Root Crops</div>
                <div>Fats/Oils</div>
            </div>
        </div>

        <div class="chart-box">
            <div class="chart-title">
                Graphical Representation of the Nutritional Status of Children
                <?php echo isset($_SESSION['active_cdc_name']) ? htmlspecialchars(strtoupper($_SESSION['active_cdc_name'])) : ''; ?>
            </div>

            <div class="fake-chart">
                <div class="fake-bar bar-mix-1"></div>
                <div class="fake-bar bar-mix-2"></div>
                <div class="fake-bar bar-mix-3"></div>
                <div class="fake-bar bar-mix-4"></div>
                <div class="fake-bar bar-mix-5"></div>
                <div class="fake-bar bar-mix-6"></div>
                <div class="fake-bar bar-mix-7"></div>
                <div class="fake-bar bar-mix-8"></div>
                <div class="fake-bar bar-mix-9"></div>
            </div>

            <div class="chart-labels nutri">
                <div>Normal</div>
                <div>Underweight</div>
                <div>Severely Underweight</div>
                <div>Overweight</div>
                <div>Obese</div>
                <div>Stunted</div>
                <div>Severely Stunted</div>
                <div>Moderately Wasted</div>
                <div>Severely Wasted</div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var mainContent = document.getElementById('mainContent');

    if (window.innerWidth <= 991) {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
    } else {
        sidebar.classList.toggle('closed');
        mainContent.classList.toggle('full');
    }
}

function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('show');
}
</script>

</body>
</html>