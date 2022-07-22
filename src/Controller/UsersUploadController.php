<?php

namespace App\Controller;

use Auth;
use Hash;
use App\Entity\User;
use App\Entity\Brand;
use App\Entity\Sender;
use App\Entity\Company;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\AddEntity;
use App\Service\BrickPhone;
use App\Service\DbInitData;
use App\Form\UserUploadType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\BrandRepository;
use App\Repository\RouterRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\ORM\EntityManagerInterface;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
#[Route('{_locale}/uploads')]
class UsersUploadController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, UrlGeneratorInterface $urlGenerator, Services $services, BrickPhone $brickPhone,  
    EntityManagerInterface $entityManager, TranslatorInterface $translator,
    RoleRepository $roleRepository, UserRepository $userRepository, uBrand $brand, ValidatorInterface $validator,
    DbInitData $dbInitData, AddEntity $addEntity, BrandRepository $brandRepository, RouterRepository $routeRepository)
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
        $this->brandRepository   = $brandRepository;
        $this->routeRepository   = $routeRepository;
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

    #[Route('/user', name: 'users_imports', methods: ['POST', 'GET'])]
    public function importFil(Request $request, SluggerInterface $slugger, UserPasswordHasherInterface $userPasswordHasher)
    {
        /** @var UploadedFile $FILE */
            $file = $this->getParameter('avatar_directory').'reseller.csv';
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
            $startRow = 1;//count($this->userRepository->findAll()) + 2;
            $saveRow  = 0;
            for($row  = $startRow; $row <= ($startRow + 150); $row++)
            {
                $user      = New User();
                $id       = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $uid       = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $uid       =($uid) ? $uid : $id.$id. $id. $id;
               
                if ($uid) 
                {
                    $admin     = $this->userRepository->findOneByUid($worksheet->getCellByColumnAndRow(3, $row)->getValue());
                    $role_name = $this->userRepository->findOneByUid($worksheet->getCellByColumnAndRow(4, $row)->getValue());
                
                
                    $apikeyFeda    = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                    $fname         = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                    $lname     = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                    $phone     = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                    $phone     = ($phone) ? $phone : '22955724444';
                    $email     = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                    $email     = ($email) ? $email : 'phantom@'.$id.'fastermessage.com';
                    //dd($email);
                    $company   = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
                    $registre  = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
                    $ifu       = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
                    $address   = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
                    $address   = ($address) ? $address : '';
                    $sender    = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
                    $balance   = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
                    $price     = $worksheet->getCellByColumnAndRow(17, $row)->getValue();
                    $devise    = $worksheet->getCellByColumnAndRow(18, $row)->getValue();
                    $password  = $worksheet->getCellByColumnAndRow(19, $row)->getValue();
                    $created_at  = $worksheet->getCellByColumnAndRow(20, $row)->getValue();
                    $phonecode   = $worksheet->getCellByColumnAndRow(21, $row)->getValue();
                    $theme       = $worksheet->getCellByColumnAndRow(22, $row)->getValue();
                    $language    = $worksheet->getCellByColumnAndRow(23, $row)->getValue();
                    $last_login  = $worksheet->getCellByColumnAndRow(24, $row)->getValue();
                    $apikey      = $worksheet->getCellByColumnAndRow(25, $row)->getValue();
                    $recover_id  = $worksheet->getCellByColumnAndRow(26, $row)->getValue();
                    $route     = $worksheet->getCellByColumnAndRow(27, $row)->getValue();
                    $country   = $worksheet->getCellByColumnAndRow(28, $row)->getValue();
                    $style     = $worksheet->getCellByColumnAndRow(29, $row)->getValue();
                    $brand     = $worksheet->getCellByColumnAndRow(30, $row)->getValue();
                    $company_address  = $worksheet->getCellByColumnAndRow(31, $row)->getValue();
                    $affiliation   = $worksheet->getCellByColumnAndRow(33, $row)->getValue();
                    $brand_admin   = $worksheet->getCellByColumnAndRow(34, $row)->getValue();
                    $seller        = $worksheet->getCellByColumnAndRow(35, $row)->getValue();
                    $timezone      = $worksheet->getCellByColumnAndRow(36, $row)->getValue();
                    $isdlr           = $worksheet->getCellByColumnAndRow(37, $row)->getValue();
                    $default_sender  = $worksheet->getCellByColumnAndRow(38, $row)->getValue();
                    $post_pay        = $worksheet->getCellByColumnAndRow(39, $row)->getValue();
                    //dd($address, $company, $email, $id , $phone, $uid);
                    switch ($role_name) {
                        case 'ROLE_ADMIN': 
                        if ($affiliation == 1) {
                                $role = $this->roleRepository->findOneById(3);
                        }else {
                                $role = $this->roleRepository->findOneById(4);
                        }
                            break;
                        case 'ROLE_SUPER_ADMIN':
                                $role = $this->roleRepository->findOneById(2);
                            break;
                        default:
                            if ($affiliation == 1) {
                                $role = $this->roleRepository->findOneById(1);
                            }else {
                                $role = $this->roleRepository->findOneById(2);
                        }
                            break;
                    }

                    $uider = $this->userRepository->findOneByUid($uid);
                    ($uider) ? $user->setUid($this->services->numeric_generate(18)) : $user->setUid($uid);
                    
                    $user->setRole($role);
                    $user->setRoles(['ROLE_'.$role->getName()]);
                    $user->setApikey($apikey);
                    $user->setPhone($phone);
                    $user->setEmail($email);
                    $user->setBalance($balance);
        
                    $country_code  = 'BJ';
                    $countryDatas = $this->brickPhone->getCountryByCode('bj');
                    if ($countryDatas) {
                        $countryDatas  = [
                            'dial_code' => $countryDatas['dial_code'],
                            'code'      => $country_code,
                            'name'      => $countryDatas['name']
                        ];
                    }

                    $user->setStatus($this->services->status(3));
                    $user->setRouter($this->routeRepository->findOneByName('FASTERMESSAGE_MOOV'));
                    $user->setBrand($this->brandRepository->findOneByName('FASTERMESSAGE'));
                    $user->setCountry($countryDatas);
                    $user->setPaymentAccount($this->comptes);
                    $user->setProfilePhoto('default_avatar_1.png');
                    $user->setCreatedAt(new \DatetimeImmutable());
                    $user->setPassword(/*$userPasswordHasher->hashPassword($user, $referral_code)*/$password);
                    //$user->setAdmin($this->userRepository->findOneByUid($admin_id));
                    $user->setisDlr($isdlr);
                    $user->setPostPay(($post_pay) ? $post_pay : 0);
                    $user->setAffiliateManager($admin);
                    $this->userRepository->add($user, true);
                
                    $udata = [
                        'ccode' => $country_code,
                        'cname' => $countryDatas['name'],
                        'ufirstname' => $fname,
                        'ulastname'  => $lname,
                    ];
                    $this->addEntity->defaultUsetting($user,  $udata);

                    //set company profil
                    if ($company != null) { 
                        if ($company != 'URBAN TECHNOLOGY') {
                            $company1 = new Company;
                            $company1->setUid($uid)
                            ->setStatus($this->services->status(3))
                            ->setName($company)
                            ->setCreatedAt(new \DatetimeImmutable())
                            ->setEmail($email)
                            ->setIfu($ifu)
                            ->setRccm($registre)
                            ->setAddress($address)
                            ->setPhone($phone);
                        }
                        $this->em->getRepository(Company::Class)->add($company1, true);
                    }     
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

    #[Route('/brand', name: 'rands_imports', methods: ['POST', 'GET'])]
    public function importBrand(Request $request, SluggerInterface $slugger, UserPasswordHasherInterface $userPasswordHasher)
    {
        /** @var UploadedFile $FILE */
            $file = $this->getParameter('avatar_directory').'brand.csv';
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
            $startRow = count($this->brandRepository->findAll())+1;
            $saveRow  = 0;
            for($row  = $startRow; $row <= ($startRow + 150); $row++)
            {
                $id        = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $logo      = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $uid       = $worksheet->getCellByColumnAndRow(4, $row)->getValue();

            
                $url            =  $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                $email          =  $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                $email_noreply  = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                $phone          = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                $admin          = $this->userRepository->findOneByUid($uid);
                //dd($admin, $uid);
                //dd($url, $email, $startRow);
                    
                $brand = new Brand();
                $brand->setUid($this->services->idgenerate(10))
                    ->setStatus($this->services->status(3))
                    ->setValidator($this->userRepository->findOneById(1))
                    ->setManager($this->userRepository->findOneByUid($uid))
                    ->setCreator($this->userRepository->findOneByUid($uid))
                    //->setDefaultSender($this->em->getRepository(Sender::Class)->findOneById(1))
                    ->setName('NRANDNAME')
                    ->setSiteUrl($url)
                    ->setFavicon($logo)
                    ->setEmail($email)
                    ->setLogo($logo)
                    ->setCommission(0)
                    ->setNoreplyEmail($email_noreply)
                    ->setPhone($phone)
                    ->setIsDefault(true)
                    ->setCreatedAt(new \DatetimeImmutable());
                    //dd($brand);
                    $this->brandRepository->add($brand, true);
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
