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
        $ref         = $this->services->idgenerate(12);
        $transaction->setUser($user);
        $transaction->setReference($ref);
        $transaction->setAmount($data['amount']);
        $transaction->setBeforeBalance($user->getBalance());
        $transaction->setAfterBalance($data['afterBalance']);
        $transaction->setStatus($this->services->status(2));
        $transaction->setCreatedAt(new \DatetimeImmutable());
        $this->transactionRepository->add($transaction);
        $this->services->addLog($this->intl->trans("Création de la transaction").' : '.$ref);
        return [
            'reference' => $ref,
            'status'    => true,
            'entity'    => $transaction
        ];
    }
    
}
