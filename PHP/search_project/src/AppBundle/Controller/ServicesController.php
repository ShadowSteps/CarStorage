<?php
/**
 * Created by PhpStorm.
 * User: Misho
 * Date: 28.12.2016 г.
 * Time: 15:25
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shadows\CarStorage\Core\ML\IndexKNNFinder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use appBundle\queryGenerator;
class ServicesController extends Controller
{
    /**
     * @Route("/services/getResults", name="query")
     */
    public function indexAction(Request $request)
    {
        $all = $request->get("all");
        $text = $request->get("text");
        $description = $request->get("description");
        $title = $request->get("title");
        $keywords = $request->get("keywords");
        $price = $request->get("price");
        $page = $request->get("page");
        $year = $request->get("year");
        $highlight = $request->get('highlight');
        $distance = $request->get("distance");
        $itemsPerPage = $request->get("pageItems");
        $options = array(
            "all" => $all,
            "text" => $text,
            "description" => $description,
            "title" => $title,
            "keywords" => $keywords,
            "price" => $price,
            "year" =>$year,
            "highlight"=>$highlight,
            "distance" =>$distance,
            "page" =>$page,
            "itemsPerPage" => $itemsPerPage
        );
        $generator = new queryGenerator("http://81.161.246.26:8080/solr/car_storage_v3/select?",$options);
        $data = $generator->performQuery();
        return new Response($data->raw_body);
    }

    /**
     * @Route("/services/getNearest", name="nearest")
     */
    public function nearestAction(Request $request)
    {
        $id = $request->get("id");
        $indexKNNFinder = new IndexKNNFinder("http://localhost:8983/solr/carstorage/");
        $data = $indexKNNFinder->FindNearest(5, $id);
        return new Response(json_encode($data));
    }
}