<?php
// {{{ICINGA_LICENSE_HEADER}}}
// {{{ICINGA_LICENSE_HEADER}}}

namespace Icinga\Module\Monitoring\DataView;

use Icinga\Module\Monitoring\Filter\MonitoringFilter;

class HostStatus extends DataView
{
    /**
     * Retrieve columns provided by this view
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            'host_name',
            'host_state',
            'host_state_type',
            'host_last_state_change',
            'host_address',
            'host_handled',
            'host_icon_image',
            'host_acknowledged',
            'host_output',
            'host_long_output',
            'host_in_downtime',
            'host_is_flapping',
            'host_last_check',
            'host_next_check',
            'host_notifications_enabled',
            'host_unhandled_service_count',
            'host_action_url',
            'host_notes_url',
            'host_last_comment',
            'host',
            'host_display_name',
            'host_alias',
            'host_ipv4',
            'host_severity',
            'host_perfdata',
            'host_does_active_checks',
            'host_accepts_passive_checks',
            'host_last_hard_state',
            'host_last_hard_state_change',
            'host_last_time_up',
            'host_last_time_down',
            'host_last_time_unreachable'
        );
    }



    public static function getTableName()
    {
        return 'status';
    }

    public function getSortRules()
    {
        return array(
            'host_name' => array(
                'order' => self::SORT_ASC
            ),
            'host_address' => array(
                'columns' => array(
                    'host_ipv4',
                    'service_description'
                ),
                'order' => self::SORT_ASC
            ),
            'host_last_state_change' => array(
                'order' => self::SORT_ASC
            ),
            'host_severity' => array(
                'columns' => array(
                    'host_severity',
                    'host_last_state_change',
                ),
                'order' => self::SORT_ASC
            )
        );
    }

    public function getFilterColumns()
    {
        return array('hostgroups', 'servicegroups', 'service_problems');
    }

    public function isValidFilterTarget($column)
    {
        if ($column[0] === '_'
            && preg_match('/^_(?:host|service)_/', $column)
        ) {
            return true;
        }
        return parent::isValidFilterTarget($column);
    }
}
