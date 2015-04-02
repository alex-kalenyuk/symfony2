<?php

namespace TrainingBundle\Services;


class StringCalculatorKata
{
    public function add($numbers)
    {
        $nums = preg_split("/(,|\\n|;)/", $numbers);
        if (count($nums) === 1) {
            return (int)$nums[0];
        } else {
            return $this->arraySum($nums);
        }
    }

    public function arraySum(array $arr)
    {
        $sum = 0;
        $negativeNums = [];
        foreach($arr as $item) {
            if (is_numeric($item)) {
                if ((int)$item > 0) {
                    $sum += $item;
                } else {
                    $negativeNums[] = $item;
                }
            }
        }

        if (count($negativeNums) > 0) {
            throw new \Exception("negatives not allowed: " . implode(",", $negativeNums));
        }

        return $sum;
    }
}