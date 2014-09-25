<?php

use Lw\Infrastructure\Persistence\Doctrine\EntityManagerFactory;

class Application
{
    public static function bootstrap()
    {
        $app = new Silex\Application();

        $app['debug'] = true;

        $app['em'] = $app->share(function() {
            return (new EntityManagerFactory())->build();
        });

        $app['em_session'] = $app->share(function($app) {
            return new \Lw\Infrastructure\Persistence\Doctrine\Session($app['em']);
        });

        $app['user_repository'] = $app->share(function($app) {
            return $app['em']->getRepository('Lw\Infrastructure\Domain\Model\User\DoctrineUser');
        });

        $app['wish_repository'] = $app->share(function($app) {
            return $app['em']->getRepository('Lw\Infrastructure\Domain\Model\Wish\DoctrineWishEmail');
        });

        $app['user_factory'] = $app->share(function() {
            return new \Lw\Infrastructure\Domain\Model\User\DoctrineUserFactory();
        });


        $app['add_wish_application_service'] = $app->share(function($app) {
            return new \Lw\Application\Service\Wish\AddWishService(
                $app['user_repository'],
                $app['wish_repository']
            );
        });

        $app['update_wish_application_service'] = $app->share(function($app) {
            return new \Lw\Application\Service\Wish\UpdateWishService(
                $app['user_repository'],
                $app['wish_repository']
            );
        });

        $app['delete_wish_application_service'] = $app->share(function($app) {
            return new \Lw\Application\Service\Wish\DeleteWishService(
                $app['user_repository'],
                $app['wish_repository']
            );
        });

        $app['sign_in_user_application_service'] = $app->share(function($app) {
            return new \Lw\Application\TransactionalService(
                new \Lw\Application\Service\User\SignInUserService(
                    $app['user_repository'],
                    $app['user_factory']
                ),
                $app['em_session']
            );
        });

        $app['event_repository'] = $app->share(function($app) {
            return $app['em']->getRepository('Lw\Domain\Model\Event');
        });

        $app['tx_application_service'] = $app->share(function($app) {
            return new \Lw\Application\UseCase\TransactionalUseCaseFactory(
                new \Lw\Infrastructure\Persistence\Doctrine\Session($app['em'])
            );
        });

        $app->register(new Silex\Provider\SessionServiceProvider());
        $app->register(new Silex\Provider\UrlGeneratorServiceProvider());
        $app->register(new Silex\Provider\FormServiceProvider());
        $app->register(
            new Silex\Provider\TwigServiceProvider(),
            array(
                'twig.path' => __DIR__.'/../../src/Lw/Infrastructure/Ui/Twig/Views',
            )
        );

        return $app;
    }
}
