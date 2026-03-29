<?php

if (!function_exists('nnc_normalize_sex')) {
    function nnc_normalize_sex($sex){
        $sex = strtolower(trim((string)$sex));

        if($sex === 'male' || $sex === 'm' || $sex === 'boy' || $sex === 'boys'){
            return 'male';
        }

        if($sex === 'female' || $sex === 'f' || $sex === 'girl' || $sex === 'girls'){
            return 'female';
        }

        return '';
    }
}

if (!function_exists('nnc_round_height_to_half_cm')) {
    function nnc_round_height_to_half_cm($height_cm){
        return round(((float)$height_cm) * 2) / 2;
    }
}

if (!function_exists('nnc_find_wfa_row')) {
    function nnc_find_wfa_row(mysqli $conn, $sex, $age_months){
        $sex = nnc_normalize_sex($sex);
        $age_months = (int)$age_months;

        $stmt = $conn->prepare("
            SELECT *
            FROM growth_wfa
            WHERE LOWER(sex) = LOWER(?) AND age_months = ?
            LIMIT 1
        ");
        $stmt->bind_param("si", $sex, $age_months);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }
}

if (!function_exists('nnc_find_hfa_row')) {
    function nnc_find_hfa_row(mysqli $conn, $sex, $age_months){
        $sex = nnc_normalize_sex($sex);
        $age_months = (int)$age_months;

        $stmt = $conn->prepare("
            SELECT *
            FROM growth_hfa
            WHERE LOWER(sex) = LOWER(?) AND age_months = ?
            LIMIT 1
        ");
        $stmt->bind_param("si", $sex, $age_months);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }
}

if (!function_exists('nnc_find_wflh_row')) {
    function nnc_find_wflh_row(mysqli $conn, $sex, $height_cm){
        $sex = nnc_normalize_sex($sex);
        $height_cm = nnc_round_height_to_half_cm($height_cm);

        $stmt = $conn->prepare("
            SELECT *
            FROM growth_wflh
            WHERE LOWER(sex) = LOWER(?) AND height_cm = ?
            LIMIT 1
        ");
        $stmt->bind_param("sd", $sex, $height_cm);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }
}

if (!function_exists('nnc_get_wfa_status')) {
    function nnc_get_wfa_status(mysqli $conn, $sex, $age_months, $weight){
        $row = nnc_find_wfa_row($conn, $sex, $age_months);

        if(!$row){
            return '--';
        }

        $weight = (float)$weight;

        if($weight <= (float)$row['severely_underweight_max']){
            return 'Severely Underweight';
        }

        if($weight >= (float)$row['underweight_from'] && $weight <= (float)$row['underweight_to']){
            return 'Underweight';
        }

        if($weight >= (float)$row['normal_from'] && $weight <= (float)$row['normal_to']){
            return 'Normal';
        }

        if($weight >= (float)$row['overweight_min']){
            return 'Overweight';
        }

        return '--';
    }
}

if (!function_exists('nnc_get_hfa_status')) {
    function nnc_get_hfa_status(mysqli $conn, $sex, $age_months, $height_cm){
        $row = nnc_find_hfa_row($conn, $sex, $age_months);

        if(!$row){
            return '--';
        }

        $height_cm = (float)$height_cm;
        $stunted_from = (float)$row['stunted_from'];
        $stunted_to = (float)$row['stunted_to'];
        $normal_from = (float)$row['normal_from'];
        $normal_to = (float)$row['normal_to'];
        $tall_min = (float)$row['tall_min'];

        if($height_cm < $stunted_from){
            return 'Severely Stunted';
        }

        if($height_cm >= $stunted_from && $height_cm <= $stunted_to){
            return 'Stunted';
        }

        if($height_cm >= $normal_from && $height_cm <= $normal_to){
            return 'Normal';
        }

        if($height_cm >= $tall_min){
            return 'Tall';
        }

        return '--';
    }
}

if (!function_exists('nnc_get_wflh_status')) {
    function nnc_get_wflh_status(mysqli $conn, $sex, $age_months, $height_cm, $weight){
        $age_months = (int)$age_months;

        if($age_months < 24 || $age_months > 71){
            return '--';
        }

        $row = nnc_find_wflh_row($conn, $sex, $height_cm);

        if(!$row){
            return '--';
        }

        $weight = (float)$weight;

        if($weight <= (float)$row['severely_wasted_max']){
            return 'Severely Wasted';
        }

        if($weight >= (float)$row['wasted_from'] && $weight <= (float)$row['wasted_to']){
            return 'Wasted';
        }

        if($weight >= (float)$row['normal_from'] && $weight <= (float)$row['normal_to']){
            return 'Normal';
        }

        if($weight >= (float)$row['overweight_from'] && $weight <= (float)$row['overweight_to']){
            return 'Overweight';
        }

        if($weight >= (float)$row['obese_min']){
            return 'Obese';
        }

        return '--';
    }
}

if (!function_exists('nnc_get_overall_status')) {
    function nnc_get_overall_status($wfa_status, $hfa_status, $wflh_status){
        $statuses = [
            trim((string)$wfa_status),
            trim((string)$hfa_status),
            trim((string)$wflh_status)
        ];

        foreach ($statuses as $status){
            if($status === 'Severely Wasted') return 'Severely Wasted';
            if($status === 'Obese') return 'Obese';
            if($status === 'Severely Underweight') return 'Severely Underweight';
            if($status === 'Severely Stunted') return 'Severely Stunted';
        }

        foreach ($statuses as $status){
            if($status === 'Wasted') return 'Wasted';
            if($status === 'Underweight') return 'Underweight';
            if($status === 'Stunted') return 'Stunted';
            if($status === 'Overweight') return 'Overweight';
        }

        foreach ($statuses as $status){
            if($status === 'Normal') return 'Normal';
        }

        return '--';
    }
}
?>