<?php


namespace Drupal\rir_interface\Form;


use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class CurrencyConverterSettingsForm extends ConfigFormBase
{

    const SETTINGS = 'rir_interface.currency_converter.settings';

    protected function getEditableConfigNames(): array
    {
        return [static::SETTINGS];
    }

    public function getFormId(): string
    {
        return 'rir_interface_currency_converter_settings';
    }

    public function buildForm(array $form, FormStateInterface $form_state): array
    {
        $config = $this->config(static::SETTINGS);
        $form['currency_converter_url'] = [
            '#type' => 'textfield',
            '#title' => t('URL to currency converter API'),
            '#default_value' => $config->get('currency_converter_url'),
            '#description' => t('Enter a valid and absolute URL.')
        ];
        return parent::buildForm($form, $form_state);
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if (!$form_state->isValueEmpty('currency_converter_url')) {
            if (!UrlHelper::isValid($form_state->getValue('currency_converter_url'), true)) {
                $form_state->setErrorByName('currency_converter_url', 'Invalid URL. This url has to be valid and absolute.');
            }
        }
        parent::validateForm($form, $form_state);
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->configFactory->getEditable(static::SETTINGS)
            ->set('currency_converter_url', $form_state->getValue('currency_converter_url'))
            ->save();
        parent::submitForm($form, $form_state);
    }
}