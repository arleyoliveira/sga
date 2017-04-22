<?php
/**
 * Created by PhpStorm.
 * User: arley
 * Date: 02/04/17
 * Time: 16:08
 */

namespace SistemaAcesso\AccessControlBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SistemaAcesso\AccessControlBundle\Entity\Access;
use SistemaAcesso\SchoolBundle\Entity\Environment;
use SistemaAcesso\SchoolBundle\Utility\Exception\EnviromentInvalidException;
use SistemaAcesso\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class AccessControlController
 * @package SistemaAcesso\AccessControlController\Controller
 * @Template()
 * @Route("/access-control")
 */
class AccessControlController extends Controller
{

    /**
     * @Route("/check.{_format}", name="access_control_check", defaults={"_format": "json"})
     * @Method("POST")
     */
    public function checkAction(Request $request)
    {
        $httpCode = 200;
        try {

            $str = file_get_contents('php://input');
            $dados = \json_decode($str);

            $identificationCard = '';
            $password = '';
            $environmentIdentification = '';

            if(property_exists($dados, 'identificationCard')){
                $identificationCard = $dados->identificationCard;
            }

            if(property_exists($dados, 'password')){
                $password = $dados->password;
            }

            if(property_exists($dados, 'environmentIdentification')){
                $environmentIdentification = $dados->environmentIdentification;
            }

            if($identificationCard and $environmentIdentification){
                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository(User::class)->findOneBy(['identificationCard' => $identificationCard]);
                $environment = $em->getRepository(Environment::class)->findOneBy(['identification' => $environmentIdentification]);

                $access = $em->getRepository(Access::class)->findOneBy(['environment' => $environment, 'user' => $user, 'isOut' => false]);

                if ($user and $environment and $this->getEnvironmentService()->checkOperation($environment)) {
                    if(!$access){
                        $access = new Access();
                        $access
                            ->setEnvironment($environment)
                            ->setUser($user)
                        ;

                        $em->persist($access);
                        $em->flush();



                    }

                    $result = [
                        'c' => 'a5e2y6',
                        'p' => $user->getName()
                    ];
                } else {
                    $result = [
                        'c' => 'a5e2y6',
                        'p' => 'Usuario Invalido'
                    ];
                }
            }else{
                throw new \InvalidArgumentException('Dados Invalidos!');
            }


        } catch (\InvalidArgumentException $e) {
            $httpCode = 500;
            $result = [
                'c' => '00000000',
                'p' => "Falha! " . $e->getMessage()
            ];
        } catch (\Exception $e) {
            $httpCode = 500;
            $result = [
                'c' => '00000000',
                'p' => "Falha! " . $e->getMessage()
            ];
        }


        return new Response(\json_encode($result), $httpCode);
    }

    /**
     * @Route("/checkout.{_format}", name="access_control_checkout", defaults={"_format": "json"})
     * @Method("POST")
     */
    public function checkOutAction(Request $request)
    {

        $httpCode = 200;
        try {
            $em = $this->getDoctrine()->getManager();

            $str = file_get_contents('php://input');
            $dados = \json_decode($str);

            $identificationCard = '';
            $password = '';
            $environmentIdentification = '';

            if(property_exists($dados, 'identificationCard')){
                $identificationCard = $dados->identificationCard;
            }

            if(property_exists($dados, 'password')){
                $password = $dados->password;
            }

            if(property_exists($dados, 'environmentIdentification')){
                $environmentIdentification = $dados->environmentIdentification;
            }

            if($identificationCard and $environmentIdentification){
                $user = $em->getRepository(User::class)->findOneBy(['identificationCard' => $identificationCard]);
                $environment = $em->getRepository(Environment::class)->findOneBy(['identification' => $environmentIdentification]);

                $access = $em->getRepository(Access::class)->findOneBy(['environment' => $environment, 'user' => $user, 'isOut' => false]);

                if($access){
                    $access
                        ->setOutDate(new \DateTime('now'))
                        ->setIsOut(true)
                    ;

                    $em->persist($access);
                    $em->flush();

                    $result = [
                        'success' => true,
                        'mensagem' => "Operacao realizada com sucesso!"
                    ];
                }else{
                    $httpCode = 404;
                    $result = [
                        'success' => true,
                        'mensagem' => "Não encontrou nenhum registro para este usuário no ambiente informado!"
                    ];
                }
            }else{
                throw new \InvalidArgumentException('Dados Invalidos!');
            }


        } catch (\Exception $e) {
            $httpCode = 500;
            $result = [
                'c' => '00000000',
                'p' => "Falha! " . $e->getMessage()
            ];
        }


        return new Response(\json_encode($result), $httpCode);
    }

    /**
     * @return object|\SistemaAcesso\SchoolBundle\Service\EnvironmentService
     */
    private function getEnvironmentService(){
        return $this->get('environment.service');
    }

}