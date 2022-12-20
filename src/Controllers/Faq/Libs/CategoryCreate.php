<?php
namespace App\Controllers\Faq\Libs;

class CategoryCreate
{
    function getCat($categories)
    {
        $categ = [];
        foreach ($categories as $category){
            $categ[$category->id]['id'] = $category->id;
            $categ[$category->id]['title'] = $category->title;
            $categ[$category->id]['parent'] = $category->parent;
        }

        return $categ;
    }

    function getTree($dataset)
    {
        $tree = array();

        foreach ($dataset as $id => &$node) {
            if (isset($node['parent'])) {
                if (!$node['parent']){
                    $tree[$id] = &$node;
                } else {
                    $dataset[$node['parent']]['childs'][$id] = &$node;
                }
            }
        }

        return $tree;
    }


    // Admin
    function catCreateAdmin($categories, $category_id = null)
    {
        $cat = $this->getCat($categories);
        
        $tree = $this->getTree($cat);
        $cat_menu = $this->showCatAdmin($tree, '', $category_id);
        $categories = '<option value="null">Без категории</option>'.$cat_menu;

        return $categories;
    }

    function tplMenuAdmin($category, $str, $category_id)
    {
        if(is_null($category['parent']) || ($category['parent'] == 0)){
            $menu = '<option value="' . json_encode([0, $category['id']]) . '"';
            if ($category['id'] == $category_id) $menu .= ' selected';
            $menu .= '>' . $category['title'] . ' (ID: ' . $category['id'] . ')' . '</option>';
        } else {            
            $menu = '<option value="' . json_encode([$category['parent'], $category['id']]) . '"';
            if ($category['id'] == $category_id) $menu .= ' selected';
            $menu .= '>' . $str . ' ' . $category['title'] . ' (ID: ' . $category['id'] . ')' . '</option>';
        }

        if(isset($category['childs'])){
            $i = 1;
            for($j = 0; $j < $i; $j++){
                $str .= '→';
            }
            $i++;

            $menu .= $this->showCatAdmin($category['childs'], $str, $category_id);
        }

        return $menu;
    }

    function showCatAdmin($data, $str, $category_id)
    {
        $string = '';
        foreach($data as $item){
            $string .= $this->tplMenuAdmin($item, $str, $category_id);
        }

        return $string;
    }
}
