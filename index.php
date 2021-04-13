<?php
class Travel
{
    private $url;
    public function __construct() {
        $this->url = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels';
    }
    public function getListTravels() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        return json_decode($response);
    }
    public static function groupWithPrice($travels) {
        $groupTravel = array();
        foreach ($travels as $item) {
            $companyId = $item->companyId;
            $price = $item->price;
            if (isset($groupTravel[$companyId])) {
                $groupTravel[$companyId] += $price;
            } else {
                $groupTravel[$companyId] = $price;
            }
        }
        return $groupTravel;
    }
}
class Company
{
    private $url;
    public function __construct() {
        $this->url = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies';
    }
    public function getListCompanies() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        return json_decode($response);
    }
    public static function generateElemTree(&$treeElem,$parents_arr) {
        foreach($treeElem as $key=>$item) {
            if(!isset($item->children)) {
                $treeElem[$key]->children = array();
            }
            if(array_key_exists($key,$parents_arr)) {
                $treeElem[$key]->children = $parents_arr[$key];
                self::generateElemTree($treeElem[$key]->children,$parents_arr);
            }
        }
    }
    public static function createTree($arr, $groupTravel) {
        $parents_arr = array();

        foreach($arr as $key=>&$item) {
            $item->price = $groupTravel[$key];
            $parentId = $item->parentId;
            $id = $item->id;
            $parents_arr[$parentId][$id] = $item;
        }

        $treeElem = $parents_arr[0];
        self::generateElemTree($treeElem,$parents_arr);

        return $treeElem;
    }

}
class TestScript
{
    public function execute()
    {
        $start = microtime(true);
        $travel = new Travel();
        $travels = $travel->getListTravels();
        $groupTravel = Travel::groupWithPrice($travels);
        //
        $company = new Company();
        $companies = $company->getListCompanies();
        $newList = [];
        foreach ($companies as $item) {
            $newList[$item->id] = $item;
        }
        $companyTree = Company::createTree($newList, $groupTravel);
        print('<pre>');
        print_r($companyTree);
        print('</pre>');

        echo 'Total time: '.  (microtime(true) - $start);
    }
}

(new TestScript())->execute();



