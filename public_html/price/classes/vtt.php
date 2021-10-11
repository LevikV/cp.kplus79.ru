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

    public function checkMainCategory() {

    }

    public function getMainCategories () {
        if ($this->status) {
            $params = array("login" => VTT_LOGIN , "password" => VTT_PASSWORD);
            $main_categories = array();
            $result = $this->client->GetCategories($params);
            $items = is_array($result->GetCategoriesResult->CategoryDto)
                ? $result->GetCategoriesResult->CategoryDto
                : array($result->GetCategoriesResult->CategorysDto);
            foreach ($items as $category) {
                if ($category['ParentId'] == null) {
                    $main_categories[] = array(
                        'name' => $category['Name'],
                        'id' => $category['Id']
                    );
                }
            }
            return $main_categories;
        } else {
            return false;
        }
    }
}
