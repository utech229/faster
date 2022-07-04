<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Brand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\sLicence;
use App\Service\AddEntity;
use App\Service\BrickPhone;
use App\Service\DbInitData;
use App\Form\UserUploadType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\LicenceRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Repository\ProfessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Repository\ActivityAreaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/*#[IsGranted("ROLE_SUPER_ADMINISTRATOR")]*/
#[Route('{_locale}/home/uploads')]
class UsersUploadController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, UrlGeneratorInterface $urlGenerator, Services $services, BrickPhone $brickPhone,  
    EntityManagerInterface $entityManager, TranslatorInterface $translator,
    RoleRepository $roleRepository, UserRepository $userRepository, Brand $brand, LicenceRepository $licenceRepository, ValidatorInterface $validator,
    DbInitData $dbInitData, AddEntity $addEntity, sLicence $sLicence, ActivityAreaRepository $activityAreaRepository, ProfessionRepository $professionRepository)
    {
        $this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brickPhone      = $brickPhone;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->userRepository    = $userRepository;
        $this->roleRepository    = $roleRepository;
        $this->licenceRepository = $licenceRepository;
        $this->activityAreaRepository = $activityAreaRepository;
        $this->professionRepository = $professionRepository;
        $this->validator         = $validator;
        $this->DbInitData        = $dbInitData;
        $this->addEntity         = $addEntity;

        $this->comptes = [
			['Owner' =>'','Operator'=>'','Phone'=>'','TransactionId'=>'','Country'=>'', 'Status'=>''],
			['Banque'=>'','Country'=>'','NAccount'=>'','Swift'=>'','DocID'=>'','DocRIB'=>''],
			['Owner' =>'','NBIN'=>'','CVV2'=>'','NAccount'=>'']
		];
    }

    #[Route('', name: 'app_users_upload')]
    #[Route('{uid}/edit', name: 'app_users_upload_edit')]
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher, User $user = null): Response
    {
        $isUserAdd = (!$user) ? true : false;
        $user      = (!$user) ? new User() : $user;

        $form = $this->createForm(UserUploadType::class, $user);
        return $this->render('users_upload/index.html.twig', [
            'controller_name' => 'UsersUploadController',
            'role'            => $this->roleRepository->findAll(),
            'title'           => $this->intl->trans('Mes utilisateurs').' - '. $this->brand->index()['name'],
            'pageTitle'       => [
                [$this->intl->trans("Gestion utilisateurs"), $this->urlGenerator->generate('app_user_index')],
                [$this->intl->trans("Importation")],
            ],
            'brand'           => $this->brand->index(),
            'baseUrl'         => $this->baseUrl->init(),
            'users'           => $this->userRepository->findAll(),
            'userform'        => $form->createView(),
        ]);
    }

    #[Route('/import/file', name: 'users_import', methods: ['POST', 'GET'])]
    public function importFile(Request $request, SluggerInterface $slugger, UserPasswordHasherInterface $userPasswordHasher)
    {
        /** @var UploadedFile $FILE */
            $file = $this->getParameter('avatar_directory').'/user.xlsx';
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
                //dd($reader);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file);
            } catch(\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                die('Error loading file: '.$e->getMessage());
            }
            //File content getting in variable
            $worksheet = $spreadsheet->getActiveSheet();

            $highestRow    = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            //getting of cellulle C1 value type
            $row1Column1 = $worksheet->getCellByColumnAndRow(1, 1)->getValue();
            //Verify the type for setting the start row
            $startRow = count($this->userRepository->findAll()) + 1;
            $saveRow = 0;
            for($row = $startRow; $row <= ($startRow + 20); $row++)
            {
                $user       = New User();
                $uid            = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $referral_code  = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $godfather      = $this->userRepository->findOneByReferralCode($worksheet->getCellByColumnAndRow(3, $row)->getValue());
                $fname     = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                $lname     = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                $birthday  = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                $phone     = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                $email     = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                $gender    = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                $city      = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                $usertype  = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
                $function  = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
                $profession  = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
                $picture        = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
                $picture_v      = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
                $activity_area  = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
                $status        = $worksheet->getCellByColumnAndRow(17, $row)->getValue();
                $mixte  = $worksheet->getCellByColumnAndRow(18, $row)->getValue();
                $digital_profil  = $worksheet->getCellByColumnAndRow(19, $row)->getValue();
                $password  = $worksheet->getCellByColumnAndRow(20, $row)->getValue();
                $register_code  = $worksheet->getCellByColumnAndRow(21, $row)->getValue();
                $reset_code  = $worksheet->getCellByColumnAndRow(22, $row)->getValue();
                $licence = $worksheet->getCellByColumnAndRow(23, $row)->getValue();
                $amount_paid  = $worksheet->getCellByColumnAndRow(24, $row)->getValue();
                $earning_balance  = $worksheet->getCellByColumnAndRow(25, $row)->getValue();
                $balance = $worksheet->getCellByColumnAndRow(26, $row)->getValue();
                $devise = $worksheet->getCellByColumnAndRow(27, $row)->getValue();
                $country_name  = $worksheet->getCellByColumnAndRow(28, $row)->getValue();
                $country_code  = $worksheet->getCellByColumnAndRow(29, $row)->getValue();
                $verified = $worksheet->getCellByColumnAndRow(30, $row)->getValue();
                $last_login  = $worksheet->getCellByColumnAndRow(31, $row)->getValue();
                $connected  = $worksheet->getCellByColumnAndRow(32, $row)->getValue();
                $profil_view = $worksheet->getCellByColumnAndRow(33, $row)->getValue();
                $register_date  = $worksheet->getCellByColumnAndRow(34, $row)->getValue();
                //dd($licence);
                if ($licence == 'basic-x')  $licence = 'standard';
                
                $licence_x = $this->licenceRepository->findOneByName($licence);
                $user->setRole($licence_x->getRole());
                $user->setRoles(['ROLE_'.$licence_x->getRole()->getName()]);
                
                $user->setReferralCode($referral_code);
                $user->setLicence($licence_x);
                $user->setActivitySector($this->activityAreaRepository->findOneById(2));
                $user->setProfession($this->professionRepository->findOneById(1));
                $user->setPhone($phone);
                $user->setFirstName($fname);
                $user->setLastName($lname);
                $user->setEmail($email);
                $user->setBalance($earning_balance);
                $user->setUid(time().uniqid());
                switch ($status) {
                    case 1:
                        $scode = 3;
                        break;
                    case 0:
                        $scode = 2;
                        break;
                    case 5:
                        $scode = 5;
                        break;
                    default:
                    $scode = 2;
                        break;
                }

                $countryDatas = $this->brickPhone->getCountryByCode($country_code);
                if ($countryDatas) {
                    $countryDatas  = [
                        'dial_code' => $countryDatas['dial_code'],
                        'code'      => $country_code,
                        'name'      => $countryDatas['name']
                    ];
                }
                $user->setStatus($this->services->status($scode));
                $user->setIsVerified($verified);
                $user->setCountry($countryDatas);
                $user->setPaymentAccount($this->comptes);
                $user->setPaidAmount($amount_paid);
                $user->setProfilePhoto($picture);
                $user->setCreatedAt(new \DatetimeImmutable());
                $user->setPassword(
                // encode the plain password
                $userPasswordHasher->hashPassword($user, $referral_code));
                $user->setReferrer(($godfather == null) ? $this->userRepository->findOneById(1): $godfather);
                $this->userRepository->add($user, true);
               
                //$this->userRepository->add($user);
                $this->addEntity->defaultUsetting($user, true);
                if ($digital_profil) {
                    $this->addEntity->isProfil($user,$profil_view);
                }                     
            } 
    

        return $this->services->msg_success(
            $this->intl->trans("Importation de fichier pour une campagne."),
            $this->intl->trans("Importation de fichier effectuée."),
            [
                "filename"=>'$file->getFilename()',
                "url"=>'$url',
            ]
        );
    }
}
