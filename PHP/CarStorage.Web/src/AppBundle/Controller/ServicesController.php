<?php
/**
 * Created by PhpStorm.
 * User: Misho
 * Date: 28.12.2016 г.
 * Time: 15:25
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shadows\CarStorage\Core\Index\SOLRClient;
use Shadows\CarStorage\Core\ML\IndexKNNFinder;
use Shadows\CarStorage\Core\ML\IndexRegression;
use Shadows\CarStorage\Core\ML\RegressionModel\IndexLinearRegression;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use appBundle\queryGenerator;
class ServicesController extends Controller
{
    /**
     * @Route("/services/getNearest", name="nearest")
     */
    public function nearestAction(Request $request)
    {
        $id = $request->get("id");
        $indexKNNFinder = new IndexKNNFinder("http://192.168.50.26:8983/solr/carstorage/");
        $data = $indexKNNFinder->FindNearest(5, $id);
        return new Response(json_encode($data));
    }

    /**
     * @Route("/services/getMeanPrice", name="meanPrice")
     */
    public function meanPriceAction(Request $request)
    {
        $id = $request->get("id");
        $tempPath = __DIR__ . "/../../../../tmp/";
        $model = $tempPath . "price-model";
        $regression = new IndexRegression(unserialize(file_get_contents($model)), new SOLRClient("http://192.168.50.26:8983/solr/carstorage/"),$tempPath. "/features");
        $price = $regression->GetItemMeanPrice($id);
        return new Response(json_encode(["price" => (int)round($price)]));
    }


}