<?php
require '../config/db.php';
if(!isset($_SESSION['user'])||$_SESSION['user']['role']!=='admin'){die('<p>Admin access required. Register/login first, then set your user role to admin in phpMyAdmin.</p>');}

function uploadDrinkImage($file){
    if(!$file || $file['error']===UPLOAD_ERR_NO_FILE) return null;
    if($file['error']!==UPLOAD_ERR_OK) throw new Exception('Image upload failed.');
    if($file['size'] > 5*1024*1024) throw new Exception('Image must be 5MB or smaller.');
    $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if(!isset($allowed[$mime])) throw new Exception('Only JPG, PNG and WEBP images are allowed.');
    $dir=dirname(__DIR__).'/uploads/drinks';
    if(!is_dir($dir)) mkdir($dir,0775,true);
    $name='drink_'.bin2hex(random_bytes(8)).'.'.$allowed[$mime];
    if(!move_uploaded_file($file['tmp_name'],$dir.'/'.$name)) throw new Exception('Could not save image.');
    return 'uploads/drinks/'.$name;
}
$error='';
try {
    if(isset($_POST['add'])){
        $image=uploadDrinkImage($_FILES['image']??null);
        $pdo->prepare('INSERT INTO drinks(category_id,name,description,price,stock,image) VALUES(?,?,?,?,?,?)')->execute([$_POST['category_id'],$_POST['name'],$_POST['description'],$_POST['price'],$_POST['stock'],$image]);
        header('Location:dashboard.php'); exit;
    }
    if(isset($_POST['edit'])){
        $id=(int)$_POST['drink_id'];
        $image=uploadDrinkImage($_FILES['image']??null);
        if($image){
            $pdo->prepare('UPDATE drinks SET category_id=?,name=?,description=?,price=?,stock=?,image=? WHERE id=?')->execute([$_POST['category_id'],$_POST['name'],$_POST['description'],$_POST['price'],$_POST['stock'],$image,$id]);
        } else {
            $pdo->prepare('UPDATE drinks SET category_id=?,name=?,description=?,price=?,stock=? WHERE id=?')->execute([$_POST['category_id'],$_POST['name'],$_POST['description'],$_POST['price'],$_POST['stock'],$id]);
        }
        header('Location:dashboard.php'); exit;
    }
    if(isset($_POST['status'])){$pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$_POST['order_status'],$_POST['order_id']]);header('Location:dashboard.php');exit;}
} catch(Exception $ex){ $error=$ex->getMessage(); }
$drinks=$pdo->query('SELECT d.*,c.name category FROM drinks d LEFT JOIN categories c ON c.id=d.category_id ORDER BY d.id DESC')->fetchAll();
$cats=$pdo->query('SELECT * FROM categories')->fetchAll();
$orders=$pdo->query('SELECT * FROM orders ORDER BY id DESC LIMIT 20')->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin</title><link rel="stylesheet" href="../css/style.css"></head><body><main class="container"><div class="admin-toolbar"><div><span class="pill">Admin</span><h1>Dashboard</h1><p class="muted">Manage drinks, images, stock and customer orders.</p></div><a href="../index.php">← Shop</a></div>
<?php if($error):?><div class="alert"><?=e($error)?></div><?php endif;?>
<h2>Add Drink / Stock</h2><form class="form-card admin-form" method="post" enctype="multipart/form-data"><select name="category_id"><?php foreach($cats as $c):?><option value="<?=$c['id']?>"><?=e($c['name'])?></option><?php endforeach;?></select><input name="name" placeholder="Drink name" required><input name="description" placeholder="Description"><input type="number" step="0.01" name="price" placeholder="Price" required><input type="number" name="stock" placeholder="Stock" required><label class="upload-field">Drink image (JPG/PNG/WEBP, max 5MB)<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label><button class="btn" name="add">Add Drink</button></form>
<h2>Inventory & Images</h2><div class="table-wrap"><table class="table"><tr><th>Image</th><th>Drink</th><th>Category</th><th>Price</th><th>Stock</th><th>Edit</th></tr><?php foreach($drinks as $d):?><tr><td><?php if($d['image']):?><img class="admin-thumb" src="../<?=e($d['image'])?>" alt=""><?php else:?><span class="thumb-placeholder">🥤</span><?php endif;?></td><td><?=e($d['name'])?></td><td><?=e($d['category'])?></td><td>$<?=$d['price']?></td><td><?=$d['stock']?></td><td><details><summary>Edit</summary><form class="edit-drink-form" method="post" enctype="multipart/form-data"><input type="hidden" name="drink_id" value="<?=$d['id']?>"><select name="category_id"><?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=$d['category_id']==$c['id']?'selected':''?>><?=e($c['name'])?></option><?php endforeach;?></select><input name="name" value="<?=e($d['name'])?>" required><input name="description" value="<?=e($d['description'])?>"><input type="number" step="0.01" name="price" value="<?=$d['price']?>" required><input type="number" name="stock" value="<?=$d['stock']?>" required><input type="file" name="image" accept="image/jpeg,image/png,image/webp"><button class="btn" name="edit">Save changes</button></form></details></td></tr><?php endforeach;?></table></div>
<h2>Orders</h2><div class="table-wrap"><table class="table"><tr><th>ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Update</th></tr><?php foreach($orders as $o):?><tr><td><?=$o['id']?></td><td><?=e($o['customer_name'])?></td><td>$<?=$o['total']?></td><td><?=e($o['status'])?></td><td><form method="post"><input type="hidden" name="order_id" value="<?=$o['id']?>"><select name="order_status"><?php foreach(['Pending','Confirmed','Preparing','Ready','Out for Delivery','Delivered','Picked Up','Cancelled'] as $st):?><option <?=$o['status']===$st?'selected':''?>><?=$st?></option><?php endforeach;?></select><button name="status">Save</button></form></td></tr><?php endforeach;?></table></div></main></body></html>