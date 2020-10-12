<?php
/**
 * @var $currency_array array
 */

if(!$currency_array)
    return 'Валюта не найдена';
?>

<table class="table table-striped">
    <thead>
    <tr>
        <th scope="col">Date</th>
        <th scope="col">Currency</th>
        <th scope="col">Sell</th>
        <th scope="col">Buy</th>
    </tr>
    </thead>
    <tbody>
        <?php foreach ($currency_array as $item): ?>
            <tr>
                <td><?= $item['date']; ?></td>
                <td><?= $item['currency']; ?></td>
                <td><?= $item['buy']; ?></td>
                <td><?= $item['sale']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
