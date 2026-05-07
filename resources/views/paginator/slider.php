<?php

use Illuminate\Pagination\BootstrapPresenter;

    $presenter = new BootstrapPresenter($paginator);
?>

<div class="pagination">
    <ul class="pull left">
        <li>
        Showing
        <?php echo $paginator->getFrom(); ?>
        -
        <?php echo $paginator->getTo(); ?>
        of
        <?php echo $paginator->getTotal(); ?>
        items
        </li>
    </ul>

    <ul class="pull-right">
        <?php echo $presenter->render(); ?>
    </ul>
</div>
