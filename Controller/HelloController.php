<?php

namespace Liip\HelloBundle\Controller;

use Liip\HelloBundle\Document\Article;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * imho injecting the container is a bad practice
 * however for the purpose of this demo it makes it easier since then not all Bundles are required
 * in order to play around with just a few of the actions.
 */
class HelloController extends ContainerAware
{
    /**
     * @var Liip\ViewBundle\View\DefaultView
     */
    protected $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function indexAction($name = null)
    {
        $view = $this->view;

//        $view->setEngine('php');

        if (!$name) {
            $view->setRouteRedirect('homepage');
        } else {
            $view->setParameters(array('name' => $name));
            $view->setTemplate(array('bundle' => 'LiipHelloBundle', 'controller' => 'Hello', 'name' => 'index'));
        }

        return $view->handle();
    }

    public function phpcrAction($path)
    {
        $documentManager = $this->container->get('doctrine.phpcr_odm.document_manager');

        $repo = $documentManager->getRepository('Liip\HelloBundle\Document\Article');

        $article = $repo->find($path);
        if ($article) {
            $article->setBody((string)($article->getBody() + 1));
        } else {
            $article = new Article();
            $article->setPath($path);
            $article->setTitle('Foo');
            $article->setBody('1');
            $documentManager->persist($article);
        }

        $documentManager->flush();

        // without the ContainerWrapper we cannot alias service names "virtually"
        if (!$this->container->has('my_view')) {
            $view = $this->container->get('fos_rest.view');
        } else {
            $view = $this->container->get('my_view');
        }

        $view->setParameters(array('name' => $article->getBody()));
        $view->setTemplate(array('bundle' => 'LiipHelloBundle', 'controller' => 'Hello', 'name' => 'index'));

        return $view->handle();
    }

    public function serializerAction()
    {
        $view = $this->view;

        $article = new Article();
        $article->setPath('/foo');
        $article->setTitle('Foo');
        $article->setBody('1');

        $serializer = $view->getSerializer();
        $serializer->addNormalizer(new GetSetMethodNormalizer());

        $view->setParameters($article);

        return $view->handle();
    }

    public function facebookAction()
    {
        $view = $this->view;
        $view->setTemplate(array('bundle' => 'LiipHelloBundle', 'controller' => 'Hello', 'name' => 'facebook'));

        return $view->handle();
    }
}
