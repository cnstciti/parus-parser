<?php
namespace common\models;

/**
 * User: Viktor (based on randywendy ideas)
 * Date: 20.01.18
 */
class PolygonEngine
{

    public $map_width = 500;
    public $map_height = 500;

    public $polygon_coordinates = [] ;

    //list of the polygon lines, that together create a polygon ([x1, y1, x2, y2])
    public $polygon_bounds = [];

    public $point_to_check = [] ;

    // lines we use to create perpendicular from $bounds_box to $point_to_check
    public $perpendicularityLines = [];

    public $bounds_box = [
        'x1' => 0,
        'y1' => 0,
        'x2' => 0,
        'y2' => 0,
    ];

    public static $polygonMKAD = [
        [55.774558,37.842762],
        [55.76522,37.842789],
        [55.755723 ,37.842627],
        [55.747399 ,37.841828],
        [55.739103 ,37.841217],
        [55.730482 ,37.840175],
        [55.721939 ,37.83916],
        [55.712203 ,37.837121],
        [55.703048 ,37.83262],
        [55.694287 ,37.829512],
        [55.68529 ,37.831353],
        [55.675945 ,37.834605],
        [55.667752 ,37.837597],
        [55.658667 ,37.839348],
        [55.650053 ,37.833842],
        [55.643713,37.824787],
        [55.637347,37.814564],
        [55.62913,37.802473],
        [55.623758,37.794235],
        [55.617713,37.781928],
        [55.611755,37.771139],
        [55.604956,37.758725],
        [55.599677,37.747945],
        [55.594143,37.734785],
        [55.589234,37.723062],
        [55.583983,37.709425],
        [55.578834,37.696256],
        [55.574019,37.683167],
        [55.571999,37.668911],
        [55.573093,37.647765],
        [55.573928,37.633419],
        [55.574732,37.616719],
        [55.575816,37.60107],
        [55.5778,37.586536],
        [55.581271,37.571938],
        [55.585143,37.555732],
        [55.587509,37.545132],
        [55.5922,37.526366],
        [55.594728,37.516108],
        [55.60249,37.502274],
        [55.609685,37.49391],
        [55.617424,37.484846],
        [55.625801,37.474668],
        [55.630207,37.469925],
        [55.641041,37.456864],
        [55.648794,37.448195],
        [55.654675,37.441125],
        [55.660424,37.434424],
        [55.670701,37.42598],
        [55.67994,37.418712],
        [55.686873,37.414868],
        [55.695697,37.407528],
        [55.702805,37.397952],
        [55.709657,37.388969],
        [55.718273,37.383283],
        [55.728581,37.378369],
        [55.735201,37.374991],
        [55.744789,37.370248],
        [55.75435,37.369188],
        [55.762936,37.369053],
        [55.771444,37.369619],
        [55.779722,37.369853],
        [55.789542,37.372943],
        [55.79723,37.379824],
        [55.805796,37.386876],
        [55.814629,37.390397],
        [55.823606,37.393236],
        [55.83251,37.395275],
        [55.840376,37.394709],
        [55.850141,37.393056],
        [55.858801,37.397314],
        [55.867051,37.405588],
        [55.872703,37.416601],
        [55.877041,37.429429],
        [55.881091,37.443596],
        [55.882828,37.459065],
        [55.884625,37.473096],
        [55.888897,37.48861],
        [55.894232,37.5016],
        [55.899578,37.513206],
        [55.90526,37.527597],
        [55.907687,37.543443],
        [55.909388,37.559577],
        [55.910907,37.575531],
        [55.909257,37.590344],
        [55.905472,37.604637],
        [55.901637,37.619603],
        [55.898533,37.635961],
        [55.896973,37.647648],
        [55.895449,37.667878],
        [55.894868,37.681721],
        [55.893884,37.698807],
        [55.889094,37.712363],
        [55.883555,37.723636],
        [55.877501,37.735791],
        [55.874698,37.741261],
        [55.862464,37.764519],
        [55.861979,37.765992],
        [55.850257,37.788216],
        [55.850383,37.788522],
        [55.844167,37.800586],
        [55.832707,37.822819],
        [55.828789,37.829754],
        [55.821072,37.837148],
        [55.811599,37.838926],
        [55.802781,37.840004],
        [55.793991,37.840965],
        [55.785017,37.841576],
    ];
    public static $polygonKAD = [
        [60.0386961,29.9737233],
        [60.0386525,29.9916864],
        [60.0400962,30.0094301],
        [60.0427958,30.0264644],
        [60.0453291,30.0437284],
        [60.0476367,30.0610835],
        [60.0499523,30.0785006],
        [60.0522672,30.0959131],
        [60.0545809,30.1133173],
        [60.0568313,30.1302341],
        [60.0593023,30.1478761],
        [60.0638169,30.1634143],
        [60.0705350,30.1750409],
        [60.0777601,30.1855023],
        [60.0824856,30.2005666],
        [60.0842062,30.2182704],
        [60.0876895,30.2347984],
        [60.0932517,30.2487454],
        [60.0976799,30.2643196],
        [60.0992617,30.2818688],
        [60.0979134,30.2994376],
        [60.0956440,30.3169328],
        [60.0948792,30.3347770],
        [60.0942094,30.3527304],
        [60.0911552,30.3688795],
        [60.0835921,30.3785025],
        [60.0748864,30.3811377],
        [60.0660375,30.3848233],
        [60.0578121,30.3915181],
        [60.0521344,30.4052484],
        [60.0482732,30.4214790],
        [60.0437252,30.4342403],
        [60.0358206,30.4402183],
        [60.0263939,30.4489930],
        [60.0186255,30.4578242],
        [60.0127852,30.4714613],
        [60.0043930,30.4762880],
        [59.9955069,30.4779864],
        [59.9877469,30.4872033],
        [59.9828131,30.5017497],
        [59.9809886,30.5178641],
        [59.9765272,30.5338208],
        [59.9709605,30.5483850],
        [59.9626674,30.5538000],
        [59.9542024,30.5481667],
        [59.9459046,30.5406732],
        [59.9370180,30.5384549],
        [59.9284537,30.5340564],
        [59.9204158,30.5265279],
        [59.9113533,30.5257115],
        [59.9023524,30.5261460],
        [59.8932988,30.5237423],
        [59.8845662,30.5257974],
        [59.8758850,30.5304030],
        [59.8672780,30.5297467],
        [59.8605423,30.5178716],
        [59.8547913,30.5044175],
        [59.8539282,30.4877239],
        [59.8511255,30.4700853],
        [59.8453744,30.4576834],
        [59.8373966,30.4511164],
        [59.8302330,30.4414800],
        [59.8240544,30.4283474],
        [59.8202632,30.4115244],
        [59.8176155,30.3949289],
        [59.8158624,30.3772977],
        [59.8153357,30.3603932],
        [59.8120654,30.3436968],
        [59.8105416,30.3267503],
        [59.8158009,30.3128981],
        [59.8225173,30.2993939],
        [59.8293580,30.2878977],
        [59.8348112,30.2745206],
        [59.8318248,30.2575586],
        [59.8280942,30.2416586],
        [59.8241539,30.2259091],
        [59.8182429,30.2126977],
        [59.8126100,30.1994518],
        [59.8094457,30.1828642],
        [59.8024648,30.1725409],
        [59.7996693,30.1569381],
        [59.8039994,30.1415051],
        [59.8093713,30.1264504],
        [59.8138323,30.1112119],
        [59.8151667,30.0937604],
        [59.8161023,30.0761753],
        [59.8166313,30.0585906],
        [59.8164598,30.0405873],
        [59.8162878,30.0227853],
        [59.8177066,30.0053454],
        [59.8214838,29.9893521],
        [59.8226486,29.9719189],
        [59.8203584,29.9545289],
        [59.8171489,29.9381360],
        [59.8138350,29.9211972],
        [59.8123451,29.9036408],
        [59.8128179,29.8859291],
        [59.8133151,29.8680472],
        [59.8143817,29.8511638],
        [59.8200264,29.8369927],
        [59.8268717,29.8251344],
        [59.8347496,29.8171334],
        [59.8431133,29.8128399],
        [59.8521550,29.8083290],
        [59.8601628,29.8007931],
        [59.8661203,29.7875254],
        [59.8690228,29.7706770],
        [59.8696629,29.7527108],
        [59.8719589,29.7358413],
        [59.8759138,29.7195792],
        [59.8798209,29.7035124],
        [59.8836496,29.6876607],
        [59.8886532,29.6732653],
        [59.8961183,29.6635883],
        [59.9047998,29.6601933],
        [59.9140538,29.6602078],
        [59.9225195,29.6658431],
        [59.9311237,29.6693212],
        [59.9405023,29.6730332],
        [59.9493479,29.6765396],
        [59.9581802,29.6800418],
        [59.9671359,29.6835937],
        [59.9758395,29.6870467],
        [59.9840422,29.6911758],
        [59.9932841,29.6974126],
        [60.0009058,29.7030019],
        [60.0091798,29.7133431],
        [60.0173344,29.7249378],
        [60.0206931,29.7432585],
        [60.0210859,29.7618217],
        [60.0214519,29.7799587],
        [60.0224503,29.7977918],
        [60.0240024,29.8146025],
        [60.0256815,29.8327609],
        [60.0275125,29.8520432],
        [60.0290138,29.8682006],
        [60.0306633,29.8859542],
        [60.0322961,29.9035282],
        [60.0339272,29.9211158],
        [60.0355689,29.9388318],
        [60.0371811,29.9562296],
        [60.0368438,29.9566055],
    ];

    /*
     * Arg: polygon_coordinates
     * Example: [
                    [55.761515, 37.600375],
                    [55.759428, 37.651156],
                    [55.737112, 37.649566],
                    [55.737649, 37.597301],
                ]
     */
    public function __construct($polygon_coordinates){

        $this->loadPolygon($polygon_coordinates);

    }

    private function preparePolygonVars(){

        $this->polygon_bounds = [];

        $this->perpendicularityLines = [
            'linetop' => [], // x1, y1, x2, y2 (from, from, to, to, crosses_ticks)
            'linebottom' => [],
            'lineleft' => [],
            'lineright' => [],
        ];

        $this->bounds_box = [
            'x1' => 0,
            'y1' => 0,
            'x2' => 0,
            'y2' => 0,
        ];

    }

    public function loadPolygon($polygon_coordinates){

        //$this->point_to_check = [55.757856, 37.600000];

        $this->polygon_coordinates = $polygon_coordinates ;

        $this->preparePolygonVars();


        foreach ($this->polygon_coordinates as $_key => $_list)
            $this->polygon_coordinates[$_key]['coords'] = $this->convertLatLngIntoCoords($_list[0], $_list[1], $this->map_width, $this->map_height);


        foreach ($this->polygon_coordinates as $_key => $_list){

            $nextKey = 0 ;
            if (isset($this->polygon_coordinates[$_key + 1]))
                $nextKey = $_key + 1;


            $_list_next = $this->polygon_coordinates[$nextKey] ;

            $this->polygon_bounds[] = ['x1' => $_list['coords']['x'], 'y1' => $_list['coords']['y'], 'x2' => $_list_next['coords']['x'], 'y2' => $_list_next['coords']['y'], ];

        }



    }

    private function previewBounds($params = []){
        Global $_CFG ;

        $cachedFile = $_CFG['root'] . 'static/img/jj.png';

        $final = imagecreate($this->map_width, $this->map_height);

        $white = imagecolorallocate($final,255,255,255); // color of text

        $redColor = imagecolorallocate($final,255,0,0); // color of text
        $blueColor = imagecolorallocate($final,0,0,255); // color of text
        $greenColor = imagecolorallocate($final,0,255,0); // color of text

        //print '<pre>' . print_r($this->polygon_coordinates, true) . '</pre>';

        $zoomOffset = [
            'minX' => 0,
            'minY' => 0,
        ];

        foreach ($this->polygon_coordinates as $_point){

            if ($zoomOffset['minX'] == 0 OR $_point['coords']['x'] < $zoomOffset['minX'])
                $zoomOffset['minX'] = $_point['coords']['x'];

            if ($zoomOffset['minY'] == 0 OR $_point['coords']['y'] < $zoomOffset['minY'])
                $zoomOffset['minY'] = $_point['coords']['y'];

        }

        $this->zoomed_bounds = $this->polygon_bounds ;

        $zoomCooficient = 1000 * 8;

        foreach ($this->zoomed_bounds as $_key => $_point){

            // обнуляем систему координат
            $this->zoomed_bounds[$_key]['x1'] -= $zoomOffset['minX'];
            $this->zoomed_bounds[$_key]['y1'] -= $zoomOffset['minY'];
            $this->zoomed_bounds[$_key]['x2'] -= $zoomOffset['minX'];
            $this->zoomed_bounds[$_key]['y2'] -= $zoomOffset['minY'];

            $this->zoomed_bounds[$_key]['x1'] *= $zoomCooficient;
            $this->zoomed_bounds[$_key]['y1'] *= $zoomCooficient;
            $this->zoomed_bounds[$_key]['x2'] *= $zoomCooficient;
            $this->zoomed_bounds[$_key]['y2'] *= $zoomCooficient;

            imageline($final, $this->zoomed_bounds[$_key]['x1'], $this->zoomed_bounds[$_key]['y1'], $this->zoomed_bounds[$_key]['x2'], $this->zoomed_bounds[$_key]['y2'], $redColor);
        }

        foreach ($this->zoomed_bounds as $_point){

            //print '<pre>' . print_r($_point, true) . '</pre>';

            imagesetpixel($final, $_point['x1'], $_point['y1'], $blueColor);

        }

        if (in_array('withPerdendicular', $params)){

            foreach ($this->perpendicularityLines as $_line){

                // обнуляем систему координат
                $_line['x1'] -= $zoomOffset['minX'];
                $_line['y1'] -= $zoomOffset['minY'];
                $_line['x2'] -= $zoomOffset['minX'];
                $_line['y2'] -= $zoomOffset['minY'];

                $_line['x1'] *= $zoomCooficient;
                $_line['y1'] *= $zoomCooficient;
                $_line['x2'] *= $zoomCooficient;
                $_line['y2'] *= $zoomCooficient;

                imageline($final, $_line['x1'], $_line['y1'], $_line['x2'], $_line['y2'], $blueColor);

            }

            //print '<pre>' . print_r($this->perpendicularityLines, true) . '</pre>';

        }

        if (in_array('withDot', $params)){

            // обнуляем систему координат
            $this->point_to_check['coords']['x'] -= $zoomOffset['minX'];
            $this->point_to_check['coords']['y'] -= $zoomOffset['minY'];

            $this->point_to_check['coords']['x'] *= $zoomCooficient;
            $this->point_to_check['coords']['y'] *= $zoomCooficient;


            imagesetpixel($final, $this->point_to_check['coords']['x'], $this->point_to_check['coords']['y'], $greenColor);

            //print '<pre>' . print_r($this->perpendicularityLines, true) . '</pre>';

        }

        imagepng($final, $cachedFile);

        print '<img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($cachedFile)) . '">';

        //exit();

    }

    private function calculateBoundsBox(){

        /*
         * calculation $bounds_box
         * lowest x is x1, biggest x is x2
         * lowest y is y1, biggest y is y2
         *
         */

        foreach ($this->polygon_coordinates as $point){

            $x = $point['coords']['x'] ;
            $y = $point['coords']['y'] ;

            if ($x < $this->bounds_box['x1'] OR $this->bounds_box['x1'] == 0)
                $this->bounds_box['x1'] = $x ;

            if ($x > $this->bounds_box['x2'] OR $this->bounds_box['x2'] == 0)
                $this->bounds_box['x2'] = $x ;

            if ($y < $this->bounds_box['y1'] OR $this->bounds_box['y1'] == 0)
                $this->bounds_box['y1'] = $y ;

            if ($y > $this->bounds_box['y2'] OR $this->bounds_box['y2'] == 0)
                $this->bounds_box['y2'] = $y ;

        }

    }

    private function calculatePerpendicularityLines(){

        /*
         *
         * Проводим перпендикуляры от точки до граней boundsBox
         *
         */

        foreach ($this->perpendicularityLines as $_line_key => $list){

            $lineInfo = ['x1' => 0, 'y1' => 0, 'x2' => $this->point_to_check['coords']['x'], 'y2' => $this->point_to_check['coords']['y'], 'crosses_ticks' => 0];

            /*
             * Так как мы проводим перпендикуляры от проверямой точки к коробке полигона (bounds_box),
             * а потом проверяем пересечения получившихся перпендикуляров с гранями полигона, то может возикнуть следующая проблема:
             * если грань полигона полностью перпендикулярна (ровная!) нашему перпендикуляру, то он будет с ней не пересекаться, а соприкасаться и дальнейшая арифметика не работает.
             * Поэтому мы удлиняем каждый перпендикуляр на коэффициент $logerityCoefficient
             */
            $logerityCoefficient = 0.000002;

            if ($_line_key == 'linetop'){

                $lineInfo['x1'] = $this->point_to_check['coords']['x'] ;
                $lineInfo['y1'] = $this->bounds_box['y1'] - $this->bounds_box['y1'] * $logerityCoefficient;

            }

            if ($_line_key == 'linebottom'){

                $lineInfo['x1'] = $this->point_to_check['coords']['x'] ;
                $lineInfo['y1'] = $this->bounds_box['y2'] + $this->bounds_box['y2'] * $logerityCoefficient;

            }

            if ($_line_key == 'lineleft'){

                $lineInfo['y1'] = $this->point_to_check['coords']['y'] ;
                $lineInfo['x1'] = $this->bounds_box['x1'] - $this->bounds_box['x1'] * $logerityCoefficient;

            }

            if ($_line_key == 'lineright'){

                $lineInfo['y1'] = $this->point_to_check['coords']['y'] ;
                $lineInfo['x1'] = $this->bounds_box['x2'] + $this->bounds_box['x2'] * $logerityCoefficient ;

            }

            $this->perpendicularityLines[$_line_key] = $lineInfo;

        }

        //print '<pre>' . print_r($this->perpendicularityLines, true) . '</pre>';

    }

    public function isCrossesWith($lat, $lng){

        $this->point_to_check = [$lat, $lng];

        $this->point_to_check['coords'] = $this->convertLatLngIntoCoords($lat, $lng);


        $this->calculateBoundsBox();

        $this->calculatePerpendicularityLines();

        $isCrosses = false ;

        $this->isInsideBoundsBox = self::isPointInsideBoundsBox($this->point_to_check['coords']['x'], $this->point_to_check['coords']['y'], $this->bounds_box);

        /*
         * Необходимо посчитать количество пересечений перпендикуляров с гранями полигона. (Только если точка находится внутри bounds_box
         */
        if ($this->isInsideBoundsBox)
            foreach ($this->perpendicularityLines as $_line_key => $_perendicular)
                foreach ($this->polygon_bounds as $_point){


                    $isLinesCrosses = self::isLinesCrosses($_perendicular, $_point);

                    if ($isLinesCrosses)
                        $this->perpendicularityLines[$_line_key]['crosses_ticks']++ ;

                    //print '$isLinesCrosses: ' . (int) $isLinesCrosses . '<br/>';


                }

        //if ($this->isInsideBoundsBox)
        //self::previewBounds(['withPerdendicular', 'withDot']);

        //print '<pre>' . print_r($this->polygon_bounds, true) . '</pre>';
        //print '<pre>' . print_r($this->perpendicularityLines, true) . '</pre>';

        /*
         * Если все пересечения по 1 - точка входит в область
         */

        if ($isCrosses == false){

            // предполагаем, что точка входит в полигон. Если это не так - сбросим флаг в цикле ниже
            $isCrosses = true ;

            foreach ($this->perpendicularityLines as $_line){

                if ($_line['crosses_ticks'] == 0 AND self::isOddNumber($_line['crosses_ticks']) == false)
                    $isCrosses = false ;

            }

        }

        return $isCrosses ;
    }

    static function isOddNumber($number){

        if ($number % 2 == 0)
            return false ;

        return true ;

    }

    /*
     * Arg: latitude, longitude
     * Result: array with pixels position [x => 0, y => 0]
     */
    public function convertLatLngIntoCoords($lat, $lng){

        $x = ($lng + 180) * ($this->map_width / 360);

        // convert from degrees to radians
        $lng_rad = $lat * M_PI / 180;

        // get y value
        $mercN = log(tan((M_PI / 4) + ($lng_rad / 2 )));

        $y = ($this->map_height / 2) - ($this->map_width * $mercN / (2 * M_PI));

        return ['x' => $x, 'y' => $y];
    }

    /*
     * Does this point is inside the polygon box? (Polygon box is a raw rectangle which contain all the polygon)
     */
    static function isPointInsideBoundsBox($x, $y, $boundsBox){

        if ($x < $boundsBox['x1'] OR $x > $boundsBox['x2'])
            return false ;

        if ($y < $boundsBox['y1'] OR $y > $boundsBox['y2'])
            return false ;

        return true;

    }

    /*
     * Binary operation on two vectors
     */
    static function getVectorCrossProduct($x1, $y1, $x2, $y2){

        $result = $x1 * $y2 - $x2 * $y1;

        return $result ;
    }

    /*
     * Get know does two vectors crosses each other
     * What is P1, P2, P3, P4 vectors? (http://grafika.me/node/237)
     *
     */
    static function isLinesCrosses($line1, $line2){

        /* form vectors (coordinates) */
        $vectorP3P4 = [
            'x' => $line2['x2'] - $line2['x1'],
            'y' => $line2['y2'] - $line2['y1']
        ] ;
        $vectorP1P2 = [
            'x' => $line1['x2'] - $line1['x1'],
            'y' => $line1['y2'] - $line1['y1']
        ] ;

        $vectorP3P1 = [
            'x' => $line1['x1'] - $line2['x1'],
            'y' => $line1['y1'] - $line2['y1']
        ] ;

        $vectorP3P2 = [
            'x' => $line1['x2'] - $line2['x1'],
            'y' => $line1['y2'] - $line2['y1']
        ] ;

        $vectorP1P3 = [
            'x' => $line2['x1'] - $line1['x1'],
            'y' => $line2['y1'] - $line1['y1']
        ] ;

        $vectorP1P4 = [
            'x' => $line2['x2'] - $line1['x1'],
            'y' => $line2['y2'] - $line1['y1']
        ] ;

        $v1 = self::getVectorCrossProduct($vectorP3P4['x'], $vectorP3P4['y'], $vectorP3P1['x'], $vectorP3P1['y']);
        $v2 = self::getVectorCrossProduct($vectorP3P4['x'], $vectorP3P4['y'], $vectorP3P2['x'], $vectorP3P2['y']);

        $v3 = self::getVectorCrossProduct($vectorP1P2['x'], $vectorP1P2['y'], $vectorP1P3['x'], $vectorP1P3['y']);
        $v4 = self::getVectorCrossProduct($vectorP1P2['x'], $vectorP1P2['y'], $vectorP1P4['x'], $vectorP1P4['y']);

        if (self::isLessThenZeroWithPrecision($v1 * $v2) AND self::isLessThenZeroWithPrecision($v3 * $v4))
            return true ;

        return false ;

    }

    static function isLessThenZeroWithPrecision($a){

        $precision = 1e-15 ;
        //print $precision ;

        return 0 - $a > $precision ;

    }

}

?>