<?php
$db = new PDO('mysql:host=localhost;dbname=bb_test;charset=utf8', 'root', 'root');
$parent_cats_id = [];
$items = [];
$child_cats = [];

$cat_id = $_GET['group'];
if(empty($cat_id)){
    $cat_id = 0;
}

if ($cat_id !== 0){
    $parent_cats_id[] = $cat_id;
    get_parent_cats($cat_id);
    $parent_cats_id = array_reverse($parent_cats_id);
}else{
    $parent_cats_id[] = $cat_id;
}

function get_parent_cats($cat_id){
    global $db;
    global $parent_cats_id;

    $stmt = $db->prepare("SELECT * FROM categories WHERE `id` = ?");
    $stmt->execute([$cat_id]);
    foreach ($stmt as $v){
        $parent_cats_id[] = $v['id_parent'] ;
        get_parent_cats($v['id_parent']);
    }
}

function get_child_cats($cat_id){
    global $db;

    $stmt = $db->prepare("SELECT * FROM categories WHERE `id_parent` = ?");
    $stmt->execute([$cat_id]);
    $child_cats_id = [];
    foreach ($stmt as $v){
        $child_cats_id[] = $v['id'] ;
    }
    return $child_cats_id;
}

function add_child_cats($cat_id){
    global $child_cats;
    $child_cats_id = get_child_cats($cat_id);
    foreach ($child_cats_id as $child_cat_id){
        $child_cats[] = $child_cat_id;
        add_child_cats($child_cat_id);
    }
}

function get_child_items($cat_id){
    global $child_cats;

    $child_cats = [];
    add_child_cats($cat_id);
    $child_cats[] = $cat_id;

    return $child_cats;
}

function get_items_num($cat_id){
    global $db;
    $items_num=0;

    $child_cats = get_child_items($cat_id);
    $stmt = $db->prepare("SELECT * FROM products WHERE `id_category` = ?");
    foreach ($child_cats as $child_cat){
        $stmt->execute([$child_cat]);
        $items_num += $stmt->rowCount();
    }
    return($items_num);
}

function get_items_list($cat_id){
    global $db;
    $items = [];

    $child_cats = get_child_items($cat_id);
    $stmt = $db->prepare("SELECT * FROM products WHERE `id_category` = ?");
    foreach ($child_cats as $child_cat){
        $stmt->execute([$child_cat]);
        $stmt_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($stmt_items as $stmt_item){
            $items[] = $stmt_item;
        }
    }
    return($items);
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>MENU</title>
</head>
<body>
    <div class="flex">
        <ul class="menu">
            <a href="?group=0">Все товары</a>
            <? function menu($menu_cat_id){
                global $db;
                global $parent_cats_id;
                global $cat_id;
                $current_cat = $menu_cat_id;
                $parent_cat = ++$menu_cat_id;

                $stmt = $db->prepare("SELECT * FROM categories WHERE `id_parent` = ?");
                $stmt->execute([$current_cat]);

                $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);


                foreach ($cats as $cat) {?>
                    <li <?= ($cat['id'] === $cat_id || $parent_cats_id[$parent_cat] === $cat['id']) ? 'class="active"' : '' ?>>
                        <a href="?group=<?=$cat['id'];?>"><?=$cat['name']?></a>
                        <?= get_items_num($cat['id'])?>
                        <? if ($parent_cats_id[$parent_cat] === $cat['id']){?>
                            <ul>
                                <? menu($parent_cats_id[$parent_cat]); ?>
                            </ul>
                        <?} ?>
                    </li>
                <? }
            }
            menu($parent_cats_id[0]);
            ?>
        </ul>
        <ol>
            <?
            $items = get_items_list($cat_id);
            function items($items){
                foreach ($items as $item){?>
                    <li><?=$item['name'];?></li>
                <? }
            }
            items($items);
            ?>
        </ol>
    </div>

    <style>
        .flex{
            display: flex;
        }
        .menu .active>a{
            color: red;
        }
    </style>
</body>
</html>

