<?php
/**
 * Close surgery day and allocate shared costs to cases.
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '')) {
    CsrfUtils::csrfNotVerified();
}

$dayId = (int)($_POST['surgery_day_id'] ?? 0);
if ($dayId <= 0) {
    die(xlt('Invalid surgery day.'));
}

function fetchEligibleCases($dayId, $scope)
{
    $sql = "SELECT id, complexity_points,
                GREATEST(TIMESTAMPDIFF(MINUTE, start_time, end_time), 1) AS case_minutes
            FROM surgery_case
            WHERE surgery_day_id = ?
              AND status = 'done'";
    $params = array($dayId);

    if ($scope !== 'all') {
        $sql .= " AND specialty = ?";
        $params[] = $scope;
    }

    $res = sqlStatement($sql, $params);
    $rows = array();
    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }

    return $rows;
}

$issuesRes = sqlStatement(
    "SELECT id, qty, unit_cost, usage_type, specialty_scope, allocation_method
     FROM inventory_issue
     WHERE surgery_day_id = ?
       AND usage_type IN ('shared_batch','shared_day')",
    array($dayId)
);

$totalIssues = 0;
$totalAllocations = 0;

while ($issue = sqlFetchArray($issuesRes)) {
    $issueId = (int)$issue['id'];
    $totalCost = (float)$issue['qty'] * (float)$issue['unit_cost'];
    $method = $issue['allocation_method'];
    $scope = $issue['specialty_scope'] ?: 'all';

    if (!in_array($method, array('equal_cases', 'by_minutes', 'by_points'), true)) {
        continue;
    }

    $cases = fetchEligibleCases($dayId, $scope);
    if (empty($cases) || $totalCost <= 0) {
        continue;
    }

    sqlStatement("DELETE FROM cost_allocation WHERE issue_id = ?", array($issueId));

    $weights = array();
    $sumWeight = 0.0;
    foreach ($cases as $caseRow) {
        $caseId = (int)$caseRow['id'];
        if ($method === 'by_minutes') {
            $weight = (float)($caseRow['case_minutes'] ?? 1);
        } elseif ($method === 'by_points') {
            $weight = (float)($caseRow['complexity_points'] ?? 1);
        } else {
            $weight = 1.0;
        }

        if ($weight <= 0) {
            $weight = 1.0;
        }

        $weights[$caseId] = $weight;
        $sumWeight += $weight;
    }

    if ($sumWeight <= 0) {
        continue;
    }

    $remainingCost = $totalCost;
    $remainingQty = (float)$issue['qty'];
    $lastCaseId = array_key_last($weights);

    foreach ($weights as $caseId => $weight) {
        if ($caseId === $lastCaseId) {
            $allocatedCost = $remainingCost;
            $allocatedQty = $remainingQty;
        } else {
            $ratio = $weight / $sumWeight;
            $allocatedCost = round($totalCost * $ratio, 4);
            $allocatedQty = round((float)$issue['qty'] * $ratio, 4);
            $remainingCost -= $allocatedCost;
            $remainingQty -= $allocatedQty;
        }

        sqlStatement(
            "INSERT INTO cost_allocation (issue_id, case_id, allocated_qty, allocated_cost, rule_used)
             VALUES (?, ?, ?, ?, ?)",
            array($issueId, $caseId, $allocatedQty, $allocatedCost, $method)
        );
        $totalAllocations++;
    }

    $totalIssues++;
}

echo text(xlt('Surgery day closed.')) . ' ' .
    text(xlt('Shared issues processed')) . ': ' . text($totalIssues) . ' | ' .
    text(xlt('Allocations generated')) . ': ' . text($totalAllocations);
