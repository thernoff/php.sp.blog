<?php

namespace Application\Models;

use Application\Model;
use Application\Models\User;
use Application\Db;
use Application\Core\MultiException;

class Category extends Model
{
    const TABLE = 'categories';
    
    public $id_parent;
    public $name;
    public $alias;
    

    
    /*
    * Метод для обновления путей страниц
    */
    public static function updateFullUrl($id_category = 0)
    {
        $pages = Page::findByIdCategory($id_category);
        if (!empty($pages)){
            foreach ($pages as $page){
                $page->path = $page->getFullUrl();
                $page->save();
            }
        }
        self::updateFullUrlChildren($id_category);
    }
    
    /*
    * Метод для обновления путей страниц лежащих внутри категорий, которые лежат внутри данной категории
    */
    private static function updateFullUrlChildren($id_category = 0)
    {

        $db = Db::instance();
        $categories = $db->select("SELECT * FROM categories WHERE id_parent = :id_parent", [":id_parent" => $id_category]);

        if(!empty($categories)){
            foreach($categories as $category){
                //echo $category['id'].'<br>';
                $pages = Page::findByIdCategory($category['id']);
                if (!empty($pages)){
                    foreach ($pages as $page){
                        $page->path = $page->getFullUrl();
                        $page->save();
                    }

                //echo "<pre>";
                //var_dump($pages);
                //echo "</pre>";
                }

                //echo self::getPath($category['id']).'<br>';
                static::updateFullUrlChildren($category['id']);

            }
        }    
        //return $idsCategory;
    }
    
    /*
     * Метод для получения полного пути категории
     */
    public static function getPath($id)
    {
        $category = self::findById($id);
        $path .=  $category->alias . '/';
        if ($category->id_parent){
            $path = self::getPath($category->id_parent) . $path;
        }        
        return $path;
    }
    
    /*
     * 
     */
    public static function getParentsId($id)
    {
        $category = self::findById($id);
        $path .=  $category->id . '/';
        if ($category->id_parent){
            $path = self::getParentsId($category->id_parent) . $path;
        }        
        return $path;
    }
    
    public function deleteWithPages()
    {
        parent::delete();
        
        $db = Db::instance();
        $sql = "DELETE FROM " . pages . " WHERE id_parent = :id_parent";
        $db->execute($sql, [":id_parent" => $this->id]);
    }
}