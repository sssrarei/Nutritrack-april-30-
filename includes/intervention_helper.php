<?php

function mapToInterventionCategory($status) {
    $status = trim($status);

    $mapping = [
        'Underweight' => 'Moderately Wasted',
        'Moderately Wasted' => 'Moderately Wasted',
        'Severely Underweight' => 'Severely Wasted',
        'Severely Wasted' => 'Severely Wasted',
        'Overweight' => 'Overweight',
        'Obese' => 'Obese'
    ];

    return isset($mapping[$status]) ? $mapping[$status] : null;
}

function getInterventionGuidanceRules($category) {
    $category = trim($category);

    $rules = [
        'Moderately Wasted' => [
            'Ensure the child eats 3 meals daily at the same time (no skipping)',
            'Give small but frequent meals if the child has a poor appetite',
            'Avoid distractions during meals (no gadgets or playing)',
            'Encourage daily active play to improve appetite',
            'Observe for signs of illness (fever, diarrhea, cough)'
        ],
        'Severely Wasted' => [
            'Ensure the child eats every meal on time (never skip meals)',
            'Give small but frequent meals throughout the day',
            'Ensure adequate water intake daily',
            'Monitor closely for weakness or signs of illness',
            'Seek immediate care if the child becomes very weak or condition worsens'
        ],
        'Overweight' => [
            'Limit sweet foods and sugary drinks (replace with water)',
            'Encourage daily active play (at least 30 minutes)',
            'Avoid extra servings (control portion size)',
            'Reduce screen time (TV, phone, tablet)',
            'Maintain regular meal times (no frequent snacking)'
        ],
        'Obese' => [
            'Avoid junk foods and sugary drinks (water only if possible)',
            'Ensure daily physical activity (play, walk, movement)',
            'Control food portions (no second servings)',
            'Limit screen time and avoid long sitting periods',
            'Maintain a consistent daily routine (meals, sleep, activity)'
        ]
    ];

    return isset($rules[$category]) ? $rules[$category] : [];
}

function buildGuidanceText($rules) {
    if (empty($rules) || !is_array($rules)) {
        return '';
    }

    return implode("\n", $rules);
}

function getInterventionSeverityRank($category) {
    $ranks = [
        'Moderately Wasted' => 1,
        'Severely Wasted' => 2,
        'Overweight' => 1,
        'Obese' => 2
    ];

    return isset($ranks[$category]) ? $ranks[$category] : 0;
}

function isSameInterventionGroup($category1, $category2) {
    $wasted_group = ['Moderately Wasted', 'Severely Wasted'];
    $overweight_group = ['Overweight', 'Obese'];

    if (in_array($category1, $wasted_group) && in_array($category2, $wasted_group)) {
        return true;
    }

    if (in_array($category1, $overweight_group) && in_array($category2, $overweight_group)) {
        return true;
    }

    return false;
}

function hasImprovedInterventionStatus($previous_category, $current_category) {
    if (empty($previous_category) || empty($current_category)) {
        return false;
    }

    if (!isSameInterventionGroup($previous_category, $current_category)) {
        return true;
    }

    $previous_rank = getInterventionSeverityRank($previous_category);
    $current_rank = getInterventionSeverityRank($current_category);

    return $current_rank < $previous_rank;
}

function checkNoImprovementForTwoMonths($records) {
    if (!is_array($records) || count($records) < 3) {
        return false;
    }

    usort($records, function ($a, $b) {
        return strtotime($a['date_recorded']) <=> strtotime($b['date_recorded']);
    });

    $records = array_values($records);
    $last_index = count($records) - 1;

    $baseline_category = $records[$last_index - 2]['intervention_category'];
    $month1_category = $records[$last_index - 1]['intervention_category'];
    $month2_category = $records[$last_index]['intervention_category'];

    $improved_month1 = hasImprovedInterventionStatus($baseline_category, $month1_category);
    $improved_month2 = hasImprovedInterventionStatus($month1_category, $month2_category);

    return (!$improved_month1 && !$improved_month2);
}

