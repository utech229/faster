<?php

namespace App\Service;

use App\Service\BaseUrl;
use App\Service\Services;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TransactionRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class sTransaction extends AbstractController
{
    protected $brand;
   
	public function __construct(TranslatorInterface $intl, EntityManagerInterface $entityManager, 
    TransactionRepository $transactionRepository, Services $services)
	{
       $this->intl    = $intl;
       $this->em      = $entityManager;
       $this->transactionRepository = $transactionRepository;
       $this->services = $services;
    }

    public function create($data)
    {
        $user        = $this->getUser();
        $transaction = new Transaction();

        $transaction->setUser($user);
        $transaction->setTransactionId($data['idTransaction']);
        $transaction->setReference($data['reference']);
        $transaction->setAmount($data['amount']);
        $transaction->setBeforeBalance($user->getBalance());
        $transaction->setAfterBalance($user->getBalance() - $data['amount']);
        $transaction->setStatus($this->services->status(3));
        $transaction->setCreatedAt(new \DatetimeImmutable());
        $this->transactionRepository->add($transaction);
        $this->services->addLog($this->intl->trans("Création de la transaction").' : '.$data['reference']);
        return true;
    }
    
}
