<?php
class Vtt {
    public $status;
    private $client;

    function __construct() {

        $params = array("login" => VTT_LOGIN , "password" => VTT_PASSWORD);
        try
        {
            $this->client = new SoapClient(VTT_WSDL_URL, $params);

            if ($this->client != false) {
                $this->status = true;
            }
        }
        catch (SoapFault $E)
        {
            //write_to_log("Ошибка получения данных с портала ВТТ: ".$E->faultstring);
            $this->status = false;
        }

    }


    public function getMainCategories () {
        if ($this->status) {
            $params = array("login" => VTT_LOGIN , "password" => VTT_PASSWORD);
            $main_categories = array();

            try {
                $result = $this->client->GetCategories($params);
            } catch (SoapFault $E) {
                echo $E->faultstring;
                return false;
            }

            $items = is_array($result->GetCategoriesResult->CategoryDto)
                ? $result->GetCategoriesResult->CategoryDto
                : array($result->GetCategoriesResult->CategoryDto);
            foreach ($items as $category) {
                if ($category->ParentId == null) {
                    $main_categories[] = array(
                        'name' => $category->Name,
                        'id' => $category->Id
                    );
                }
            }
            return $main_categories;
        } else {
            return false;
        }
    }

    public function getAllCategories () {
        if ($this->status) {
            $params = array("login" => VTT_LOGIN , "password" => VTT_PASSWORD);
            try {
                $result = $this->client->GetCategories($params);
            } catch (SoapFault $E) {
                echo $E->faultstring;
                return false;
            }

            $all_categories = is_array($result->GetCategoriesResult->CategoryDto)
                ? $result->GetCategoriesResult->CategoryDto
                : array($result->GetCategoriesResult->CategoryDto);

            return $all_categories;
        } else {
            return false;
        }
    }

    public function checkMainCategories($categories) {

    }

    public function createCategory () {
        if ($this->status) {
            $categories = $this->getAllCategories();
            echo '<pre>';
            foreach ($categories as $category) {
                if (!$this->isCategoryExcept($categories, $category)) {
                    print_r($category);
                }
            }
            echo '</pre>';


        }
    }

    public function isCategoryExcept ($categories, $category) {
        if (in_array($category->Id, VTT_CATEGORY_ID_EXCEPT)) {
            return true;
        } elseif ($category->ParentId != null) {
            foreach ($categories as $parent_cat) {
                if ($parent_cat->Id == $category->ParentId) {
                    if ($this->isCategoryExcept($categories, $parent_cat)) {
                        return true;
                    } else {
                        $this->isCategoryExcept($categories, $parent_cat);
                    }
                }
            }

        } else {
            return false;
        }
    }

    private function getVttCategoryByVttId($vtt_cat_id) {

    }
}
