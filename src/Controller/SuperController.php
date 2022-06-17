<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\User;
use App\Form\UserType;
use App\Service\sBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\AddEntity;
use App\Service\BrickPhone;
use App\Service\DbInitData;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\BrandRepository;
use App\Repository\RouterRepository;
use App\Repository\SenderRepository;
use App\Repository\StatusRepository;
use App\Repository\CompanyRepository;
use App\Repository\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AuthorizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/{_locale}/super')]
class SuperController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, UrlGeneratorInterface $urlGenerator, Services $services, BrickPhone $brickPhone,
    EntityManagerInterface $entityManager, TranslatorInterface $translator,
    RoleRepository $roleRepository, UserRepository $userRepository, PermissionRepository $permissionRepository,
    AuthorizationRepository $authorizationRepository, sBrand $brand,ValidatorInterface $validator,
    DbInitData $dbInitData, AddEntity $addEntity, StatusRepository $statusRepository, BrandRepository $brandRepository,
    CompanyRepository $companyRepository, RouterRepository $routerRepository, SenderRepository $senderRepository)
    {
        $this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brickPhone      = $brickPhone;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->userRepository  = $userRepository;
        $this->roleRepository    = $roleRepository;
        $this->statusRepository  = $statusRepository;
        $this->routerRepository  = $routerRepository;
        $this->brandRepository   = $brandRepository;
        $this->companyRepository = $companyRepository;
        $this->senderRepository  = $senderRepository;
        $this->validator         = $validator;
        $this->dbInitData        = $dbInitData;
        $this->AddEntity         = $addEntity;
    }

    /*#[Route('', name: 'el_super_admin', methods: ['POST', 'GET'])]*/
    public function elsuperadmin($userPasswordHasher): Response
    {

        $existed_user = $this->userRepository->findOneById(1);
        if (!$existed_user) {
            $user = new User();
            $this->dbInitData->addRole();
            $this->dbInitData->addPermission();
            $this->dbInitData->addAuthorization();
            $role     = $this->roleRepository->findOneBy(['code' => 'SUP']);
            $phone_number = $this->brand->get()['phone']['bj'];
            $country      = 'BJ';
            //country data manage
            $countryDatas = $this->brickPhone->getCountryByCode($country);
            if ($countryDatas) {
                $countryDatas  = [
                    'dial_code' => $countryDatas['dial_code'],
                    'code'      => $country,
                    'name'      => $countryDatas['name']
                ];
            }else
                return $this->services->ajax_error_crud(
                    $this->intl->trans("Insertion du tableau de données pays"),
                    $this->intl->trans("La recherche du nom du pays à échoué : BrickPhone"),
                );

            $user->setRole($role);
            $user->setRoles(['ROLE_'.$role->getName()]);
            $user->setBalance(0);
            $user->setPhone($phone_number);
            $user->setEmail($this->brand->get()['emails']['support']);
            $user->setUid($this->services->idgenerate(30));
            $user->setApiKey($this->services->idgenerate(30));
            $user->setPostPay(1);
            $user->setIsDlr(1);
            $user->setStatus($this->statusRepository->findOneByCode(3));
            $user->setCountry($countryDatas);
            $user->setProfilePhoto('default_avatar_1.png');
            $user->setCreatedAt(new \DatetimeImmutable());
            $user->setPassword(
            // encode the plain password
            $userPasswordHasher->hashPassword($user, '@21061AdminDefault'));
            $this->userRepository->add($user);
            $this->AddEntity->defaultUsetting($user, $this->brand->get()['name'], $this->brand->get()['name']);

            $brand   = $this->brandRepository->findOneByName($this->brand->get()['name']);
            $route   = $this->routerRepository->findOneByName("Fastermessage_moov");
            $company = $this->companyRepository->findOneById(1);
            $user->setAccountManager($user)
                ->setBrand($brand)
                ->setRouter($route);
            $this->userRepository->add($user);
            return $this->services->msg_success(
                $this->intl->trans("Création du super admin : SUP-ONE"),
                $this->intl->trans("Utilisateur SUP-ONE ajouté avec succès")
            );
        }else {
            
            $brand   = $this->brandRepository->findOneById(1);
            $sender  = $this->senderRepository->findOneById(1);
            $company = $this->companyRepository->findOneById(1);
           
            $company->setManager($existed_user);
            $brand->setManager($existed_user);
            $brand->setCreator($existed_user);
            $brand->setValidator($existed_user);
            $brand->setDefaultSender($sender);
            $this->companyRepository->add($company, true);
            $this->brandRepository->add($brand, true);
            $existed_user->setAccountManager($existed_user)->setBrand($brand);
            $this->userRepository->add($existed_user);
            return $this->services->msg_success(
                $this->intl->trans("Mise à jour de la marque initiale"),
                $this->intl->trans("Marque et Entreprise initiale mise à jour")
            );
        }
        return $this->services->msg_success(
            $this->intl->trans("Mise à jour des données par défaut"),
            $this->intl->trans("Données par défaut mise à jour")
        );
    }


    #[Route('', name: 'update_initData', methods: ['POST', 'GET'])]
    public function initdata(Request $request, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $this->dbInitData->addStatus();
        $this->dbInitData->addRole();
        $this->dbInitData->addPermission();
        $this->dbInitData->addAuthorization();
        $this->dbInitData->addRoute();
        $this->dbInitData->addSender();
        $this->AddEntity->defaultBrand();
        $this->AddEntity->defaultCompany();
        $this->elsuperadmin($userPasswordHasher);
        $this->elsuperadmin($userPasswordHasher);
        $this->elsuperadmin($userPasswordHasher);
        return $this->services->msg_success(
            $this->intl->trans("Mise à jour des données par défaut"),
            $this->intl->trans("Données par défaut mise à jour")
        );
    }
}
