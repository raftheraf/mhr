<?php
/**
 * orders_ajax.php - Endpoint AJAX per modifica inline quantità e prezzi
 * Restituisce JSON: {success, new_qty|new_price, new_total, deleted}
 */
ob_start();
session_start();
define('ROOTDIR', '..');
require_once(ROOTDIR . '/includes.php');
require_once(ROOTDIR . '/waiter/waiter_start.php');
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

function ajax_out($data) {
    echo json_encode($data);
    exit;
}

if (!access_allowed(USER_BIT_WAITER) && !access_allowed(USER_BIT_CASHIER)) {
    ajax_out(array('success' => false, 'error' => 'access_denied'));
}

$command = isset($_REQUEST['command']) ? $_REQUEST['command'] : '';
$id      = isset($_REQUEST['id'])      ? (int)$_REQUEST['id'] : 0;

if (!$id) ajax_out(array('success' => false, 'error' => 'invalid_id'));

switch ($command) {

    case 'update_qty':
        $qty        = isset($_REQUEST['qty'])        ? (int)$_REQUEST['qty']        : -1;
        $suspend    = isset($_REQUEST['suspend'])    ? (int)$_REQUEST['suspend']    : 0;
        $extra_care = isset($_REQUEST['extra_care']) ? (int)$_REQUEST['extra_care'] : 0;

        if ($qty < 0) ajax_out(array('success' => false, 'error' => 'invalid_qty'));

        if ($qty === 0) {
            $err     = orders_delete(array('id' => $id));
            $deleted = true;
        } else {
            $start_data = array(
                'id'         => $id,
                'quantity'   => $qty,
                'suspend'    => $suspend,
                'extra_care' => $extra_care,
            );
            $err     = orders_update($start_data);
            $deleted = false;
        }

        $sourceid = isset($_SESSION['sourceid']) ? (int)$_SESSION['sourceid'] : 0;
        $tbl      = new table($sourceid);
        $total    = $tbl->total();

        // Genera HTML riga per aggiornare tabellalastorder lato client
        $last_order_html = '';
        if ($err == 0 && !$deleted) {
            $ord_display = new order($id);
            if ($ord_display->id) {
                $last_order_html = $ord_display->table_row($ord_display->data);
            }
        }

        ajax_out(array(
            'success'         => ($err == 0),
            'new_qty'         => $qty,
            'new_total'       => $total,
            'deleted'         => $deleted,
            'last_order_html' => $last_order_html,
        ));
        break;

    case 'update_price':
        if (!access_allowed(USER_BIT_MONEY)) {
            ajax_out(array('success' => false, 'error' => 'access_denied'));
        }

        $price_raw = isset($_REQUEST['price']) ? $_REQUEST['price'] : '0';
        $price_raw = str_replace(',', '.', $price_raw);
        $price     = (float)$price_raw;

        $ord = new order($id);
        $qty = (int)$ord->data['quantity'];

        $start_data = array(
            'id'       => $id,
            'quantity' => $qty,
            'price'    => $price,
        );
        $err = orders_update($start_data);

        $sourceid = isset($_SESSION['sourceid']) ? (int)$_SESSION['sourceid'] : 0;
        $tbl      = new table($sourceid);
        $total    = $tbl->total();

        ajax_out(array(
            'success'   => ($err == 0),
            'new_price' => sprintf('%01.2f', $price),
            'new_total' => $total,
        ));
        break;

    case 'delete':
        $err      = orders_delete(array('id' => $id));
        $sourceid = isset($_SESSION['sourceid']) ? (int)$_SESSION['sourceid'] : 0;
        $tbl      = new table($sourceid);
        $total    = $tbl->total();
        ajax_out(array(
            'success'   => ($err == 0),
            'new_total' => $total,
            'deleted'   => true,
        ));
        break;

    default:
        ajax_out(array('success' => false, 'error' => 'unknown_command'));
}
