<?php

    function get() {
        $schedules = wp_get_schedules();
        uasort( $schedules, function( array $a, array $b ) {
            return ( $a['interval'] - $b['interval'] );
        } );

        array_walk( $schedules, function( array &$schedule, $name ) {
            $schedule['name'] = $name;
        } );

        return $schedules;
    }

    function event_get() {
        $crons  = _get_cron_array();
        $events = array();

        if ( empty( $crons ) ) {
            return array();
        }

        foreach ( $crons as $time => $cron ) {
            foreach ( $cron as $hook => $dings ) {
                foreach ( $dings as $sig => $data ) {

                    // This is a prime candidate for a Crontrol_Event class but I'm not bothering currently.
                    $events[ "$hook-$sig-$time" ] = (object) array(
                        'hook'     => $hook,
                        'time'     => $time, // UTC
                        'sig'      => $sig,
                        'args'     => $data['args'],
                        'schedule' => $data['schedule'],
                        'interval' => isset( $data['interval'] ) ? $data['interval'] : null,
                    );

                }
            }
        }

        // Ensure events are always returned in date descending order.
        // External cron runners such as Cavalcade don't guarantee events are returned in order of time.
        uasort( $events, function( $a, $b ) {
            if ( $a->time === $b->time ) {
                return 0;
            } else {
                return ( $a->time > $b->time ) ? 1 : -1;
            }
        } );

        $eventHooks = [
            'officegestProductsSync',
            'syncImagens',
            'syncOfficeGestArticles',
            'syncEcoautoProcesses',
            'syncEcoautoParts',
            'SyncOfficeGestQueue',
        ];

        foreach ($events as $key => $event){
            if(!in_array($event->hook, $eventHooks)) {
                unset($events[$key]);
            }
        }

        return $events;
    }

    $get_schedules = get();
    $get_events = event_get();

    $getQueue = \OfficeGest\OfficeGestDBModel::getQueue();
?>

<style>
    table.dataTable tbody td.select-checkbox::before, table.dataTable tbody td.select-checkbox::after, table.dataTable tbody th.select-checkbox::before, table.dataTable tbody th.select-checkbox::after {
        left: 90% !important;
    }
    .mt-3{
        margin-top: 3em;
    }
    .mt-2{
        margin-top: 2em;
    }
    .column-width-200{
        width: 200px;
    }
</style>
<div class="wrap">
    <h3><?= __( "Cron Events" ) ?></h3>

    <hr>
    <table class='wp-list-table widefat fixed striped' id="cron_events">
        <thead>
        <tr>
            <th><a>Hook</a></th>
            <th><a>Next Run</a></th>
            <th><a>Recurrence</a></th>
        </tr>
        </thead>
        <tbody>
            <?php if (!empty($get_events) && is_array($get_events)) : ?>

                <!-- Lets draw a list of all the available orders -->
                <?php foreach ($get_events as $get_event) : ?>
                    <tr>
                        <td><?= $get_event->hook ?></td>
                        <td><?= gmdate("Y-m-d H:i:s", $get_event->time) ?></td>
                        <td><?= $get_event->schedule ?></td>
                    </tr>
                <?php endforeach; ?>

            <?php else : ?>
                <tr>
                    <td colspan="7">
                        <?= __("Não foram encontadas crons!") ?>
                    </td>
                </tr>

            <?php endif; ?>
        </tbody>
    </table>

    <h3 class="mt-3"><?= __( "Cron Queue" ) ?></h3>

    <hr>
    <table class="wc_status_table wc_status_table--tools widefat">
        <tbody class="tools">
            <tr>
                <th style="padding: 2rem">
                    <strong class="name"><?= __('Forçar Cron queue') ?></strong>
                    <p class='description'><?= __('Forçar cron queue a correr o proximo evento') ?></p>
                </th>
                <td class="run-tool" style="padding: 2rem; text-align: right">
                    <a class="button button-large"
                       href='<?= admin_url('admin.php?page=officegest&tab=crons&action=runofficegestqueue') ?>'>
                        <?= __('Forçar queue') ?>
                    </a>
                </td>
            </tr>
        </tbody>
    </table>

    <table class='wp-list-table widefat fixed striped mt-2' id="cron_in_queue">
        <thead>
        <tr>
            <th class="column-width-200"><a>Tipo de Importação</a></th>
            <th><a>Valores</a></th>
            <th class="column-width-200"><a>Criado em</a></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($getQueue) && is_array($getQueue)) : ?>

            <!-- Lets draw a list of all the available orders -->
            <?php foreach ($getQueue as $g) : ?>
                <tr>
                    <td><?= $g['type'] ?></td>
                    <td><?= $g['process_values'] ?></td>
                    <td><?= $g['created_at'] ?></td>
                </tr>
            <?php endforeach; ?>

        <?php else : ?>
            <tr>
                <td colspan="7">
                    <?= __("Não foram encontrados processos em lista de espera!") ?>
                </td>
            </tr>

        <?php endif; ?>
        </tbody>
    </table>
</div>