<?php

/**
 * A class created so that FOSUserBundle sends emails via PostMark
 *
 */
namespace AppBundle\Mailer;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use Postmark\PostmarkClient;
use Postmark\Models\PostmarkException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class FOSMailer implements MailerInterface
{

    protected $router;
    protected $container;

    public function __construct(Container $container, \Twig_Environment $twig, RouterInterface $router)
    {
        $this->container = $container;
        $this->twig = $twig;
        $this->router = $router;
    }

    /**
     * @param UserInterface $user
     */
    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $template  = $this->container->getParameter('fos_user.registration.confirmation.template');
        $postmarkApiKey = getenv('SYMFONY__POSTMARK_API_KEY');

        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);

        $message = $this->twig->render(
            $template,
            array(
                'user' => $user,
                'confirmationUrl' => $url
            )
        );

        $fromEmail = 'hello@lend-engine.com';
        $toEmail = $user->getEmail();

        try {
            $client = new PostmarkClient($postmarkApiKey);
            $client->sendEmail(
                "Lend Engine <{$fromEmail}>",
                $toEmail,
                "Confirm your registration.",
                $message
            );
        } catch (PostmarkException $e) {

        } catch (\Exception $e) {

        }
    }

    /**
     * @param UserInterface $user
     */
    public function sendResettingEmailMessage(UserInterface $user)
    {
        $template       = $this->container->getParameter('fos_user.resetting.email.template');
        $postmarkApiKey = getenv('SYMFONY__POSTMARK_API_KEY');

        $url = $this->router->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);

        $message = $this->twig->render(
            $template,
            array(
                'user' => $user,
                'confirmationUrl' => $url
            )
        );

        $fromEmail = 'hello@lend-engine.com';
        $toEmail = $user->getEmail();

        try {
            $client = new PostmarkClient($postmarkApiKey);
            $client->sendEmail(
                "Lend Engine <{$fromEmail}>",
                $toEmail,
                "Reset your password.",
                $message
            );
        } catch (PostmarkException $e) {

        } catch (\Exception $e) {

        }
    }
}