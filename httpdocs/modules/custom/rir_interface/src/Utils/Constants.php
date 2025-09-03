<?php
/**
 * Created by PhpStorm.
 * User: medar
 * Date: 22/12/2017
 * Time: 01:39
 */

namespace Drupal\rir_interface\Utils;


interface Constants
{
    const USD_RWF_EXCHANGE_RATE = 'latest_usd_rwf_rate';
    const LATEST_DAY_EXCHANGE_RATE = 'latest_day_exchange_rate';
    const ADVERT_CREATED = 'advert_insert_alert';
    const ADVERT_VALIDATED = 'advert_first_published';
    const ADVERT_VALIDATED_NOTIFY_PR = 'advert_validated_notify_pr';
    const PROPOSED_ADVERTS_TO_PR = 'proposed_adverts_to_pr';
    const CAMPAIGN_ALERT_EMAIL = 'send_campaign_email';

    const SECTOR = 'sector';
    const DISTRICT = 'district';
    const PROVINCE = 'province';
}
