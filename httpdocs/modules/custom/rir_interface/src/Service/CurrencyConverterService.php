<?php


namespace Drupal\rir_interface\Service;


use Drupal;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\rir_interface\Form\CurrencyConverterSettingsForm;
use Drupal\rir_interface\Utils\Constants;
use GuzzleHttp\Exception\RequestException;

final class CurrencyConverterService
{
    public function getUsdRwfRate() {
        $thisMonth = (new DrupalDateTime())->format('mY');
        $localRate = Drupal::state()->get(Constants::USD_RWF_EXCHANGE_RATE);
        $latestDayRate = Drupal::state()->get(Constants::LATEST_DAY_EXCHANGE_RATE);

        if (isset($localRate) && trim($localRate) !== '' && $latestDayRate === $thisMonth) {
            return $localRate;
        }

        $converterUrl = Drupal::config(CurrencyConverterSettingsForm::SETTINGS)->get('currency_converter_url');
        if (isset($converterUrl) and trim($converterUrl) !== '') {
            try {
                $response = Drupal::httpClient()->get($converterUrl);
                if ($response->getStatusCode() === 200) {
                    $rate = Json::decode($response->getBody()->getContents())['USD_RWF'];
                    Drupal::state()->set(Constants::USD_RWF_EXCHANGE_RATE, $rate);
                    Drupal::state()->set(Constants::LATEST_DAY_EXCHANGE_RATE, $thisMonth);
                    Drupal::logger('CurrencyConverterService')
                        ->info(t('Latest currency rate: 1USD => :rate RWF', [':rate' => $rate]));
                    return $rate;
                } else {
                    Drupal::logger('CurrencyConverterService')
                        ->error(t('Currency Converter Error. Code: :code | Message: :message',
                            [
                                ':code' => $response->getStatusCode(),
                                ':message' => $response->getReasonPhrase()
                            ]));
                }
            } catch (RequestException $e) {
                Drupal::logger('CurrencyConverterService')->warning('Currency API not available: ' . $e->getMessage());
            }

        }
        return $localRate;
    }
}
