<?php

namespace App\Service;

use App\Service\BaseUrl;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class sBrand extends AbstractController
{
    protected $brand;

	public function __construct(TranslatorInterface $intl, BaseUrl $baseUrl)
	{
       $this->intl    = $intl;
       $this->baseUrl = $baseUrl;
    }

    public function get(){
       $brandData = [
           'name'               => 'FASTERMESSAGE',
           'base_url'           =>  $this->baseUrl->init(),
           'slogan'             => $this->intl->trans('Vos SMS partout dans le monde'),
           'logo_link'          => $this->baseUrl->init().'/vitrine/img/logo.png',
           'text_logo_link'     => $this->baseUrl->init().'/vitrine/img/logo-text.png',
           'white_logo_link'    => $this->baseUrl->init().'/vitrine/img/logo-white.png',
           'favicon_link'       => $this->baseUrl->init().'/vitrine/img/favicon.png',
           'apple_touch_icon_link' => $this->baseUrl->init().'/vitrine/img/apple-touch-icon.png',
           'logo_cover_link'    => $this->baseUrl->init().'/vitrine/img/logo-cover.jpg',
           'phone'              => [
                                    'bj' => '+22952735555',
                                ],
            'socials'           => [
                'fb_link'         => 'https://facebook.com/fastermessage.me',
                'insta_link'      => 'https://instagram.com/fastermessage.me',
                'whatsapp_link'   => 'https://facebook.com/+22952735555',
                'facebook'        => 'https://facebook.com/fastermessage.me',
                'instagram'       => 'https://instagram.com/fastermessage.me',
                'whatsapp'        => 'https://facebook.com/+22952735555',
                'youtube'         => '',
            ],
            'identifier'       => [
                'ifu'  => 'RB/COT/16 B 1570',
                'rccm' => 3201641186710
            ],
            'emails'           => [
                'contact'      => 'contact@fastermessage.com',
                'support'      => 'support@fastermessage.com',
                'info'         => 'info@fastermessage.com',
                'sale'         => 'sale@fastermessage.com',
            ],
           'description'        => $this->intl->trans('Une solution simple et rapide pour envoyer des SMS en masse partout dans le monde'),
           'author'             => [
               'name'       => $this->intl->trans('URBAN TECHNOLOGY'),
               'name_code'  => $this->intl->trans('SARL'),
               'email'      => 'contact@urban-technology.net',
               'website' => 'https://urban-technology.net',
               'address' => $this->intl->trans('Abomey-Calavi - Bénin'),
           ],
           'year'               => date('Y')

       ];

       return $brandData;
    }


}
