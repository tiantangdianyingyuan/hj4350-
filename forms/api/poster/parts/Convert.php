<?php


namespace app\forms\api\poster\parts;


trait Convert
{
    function is_point_in_circle($point, $circle)
    {
        $x = abs($circle['center']['x'] - $point['x']);
        $y = abs($circle['center']['y'] - $point['y']);

        $distance = sqrt(pow($x, 2) + pow($y, 2));

        if ($distance <= $circle['radius']) {
            return true;
        } else {
            return false;
        }
    }
}