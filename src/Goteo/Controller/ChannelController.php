<?php

namespace Goteo\Controller;

use Symfony\Component\HttpFoundation\Response;

use Goteo\Application\View;
use Goteo\Model;
use Goteo\Model\Node;
use Goteo\Model\Home;
use Goteo\Model\Project;
use Goteo\Model\Banner;
use Goteo\Model\Stories;
use Goteo\Model\News;
use Goteo\Model\Post;
use // esto son entradas en portada o en footer
    Goteo\Model\Promote;
use Goteo\Model\Patron;
use Goteo\Model\Campaign;
use // convocatorias en portada
    Goteo\Model\User;
use Goteo\Model\Icon;
use Goteo\Model\Category;
use Goteo\Library\Text;
use Goteo\Library\Feed;
use Goteo\Library\Page;

class ChannelController extends \Goteo\Core\Controller {

public static function indexAction($id, $page = 'index')
    {

        $channel = Node::get($id);

        // orden de los elementos en portada
        $order = Home::getAll($id);
        $side_order = Home::getAllSide($id);

        $icons = Icon::getAll();
        $cats  = Category::getList();  // categorias que se usan en proyectos
        $hide_promotes = false;

        // Proyectos destacados primero para saber si lo meto en el buscador o no
        if (isset($order['promotes']) || isset($side_order['searcher'])) {
            $promotes  = Promote::getAll(true, $id);
        }

        // padrinos
        if (isset($order['patrons'])) {
            $patrons  =  $patrons = Patron::getInHome();
        }



        // Laterales
        // ---------------------
        if (isset($side_order['searcher'])) {
            // Selector proyectos: los destacados, los grupos de discover y los retornos
            $searcher = array();
            $discover = array();

            if (!empty($promotes)) {
                $searcher['promote'] = Text::get('node-side-searcher-promote');
                if ($page == 'about') {
                    $hide_promotes = true;
                }
            }

            // vamos sacando los 4 primeros de cada categoria (excepto promotes y excepto byreward)
            // si una categoria no tiene proyectos no la ponemos en los pastillos del buscador
            $disc_popular = Project::published('popular', 4);
            if (!empty($disc_popular)) {
                $searcher['popular'] = Text::get('node-side-searcher-popular');
                $discover['popular'] = $disc_popular;
            }

            $disc_recent = Project::published('recent', 4);
            if (!empty($disc_recent)) {
                $searcher['recent'] = Text::get('node-side-searcher-recent');
                $discover['recent'] = $disc_recent;
            }

            $disc_success = Project::published('success', 4);
            if (!empty($disc_success)) {
                $searcher['success'] = Text::get('node-side-searcher-success');
                $discover['success'] = $disc_success;
            }

            $disc_outdate = Project::published('outdate', 4);
            if (!empty($disc_outdate)) {
                $searcher['outdate'] = Text::get('node-side-searcher-outdate');
                $discover['outdate'] = $disc_outdate;
            }

            $disc_byreward = array();
            foreach ($icons as $icon => $iconData) {
                $icon_projs = \Goteo\Library\Search::params(array('reward'=>array("'$icon'"), 'node'=>true), false, 4);
                if (!empty($icon_projs)) {
                    $disc_byreward[$icon] = $icon_projs;
                }
            }
            if (!empty($disc_byreward)) {
                $searcher['byreward'] = Text::get('node-side-searcher-byreward');
                $discover['byreward'] = $disc_byreward;
            }

        }

        if (isset($side_order['categories'])) {
            // Proyectos por categorías
            $categories = array();
            foreach ($cats as $cat => $catName) {
                $cat_projs = \Goteo\Library\Search::params(array('category'=>array("'$cat'"), 'channel'=>$id), false);
                if (!empty($cat_projs)) {
                    $categories[$cat]['name'] = $catName;
                    $categories[$cat]['projects'] = $cat_projs;
                }
            }
        }

        if (isset($side_order['summary'])) {
            $summary = $channel->getSummary();
        }

        if (isset($side_order['sumcalls'])) {
            $sumcalls = $channel->getSumcalls();
        }

        if (isset($side_order['sponsors'])) {
            // Patrocinadores del nodo
            $sponsors = \Goteo\Model\Sponsor::getList($id);
        }

        // resto de centrales
        // entradas de blog
        if (isset($order['posts'])) {
            // entradas en portada
            $posts     = Post::getAll('home', $id);
        }

        // Convocatoris destacadas
        if (isset($order['calls'])) {
            $calls     = Call::getActive(3); // convocatorias en modalidad 1; inscripcion de proyectos
            $campaigns = Call::getActive(4); // convocatorias en modalidad 2; repartiendo capital riego
        }

        if ($page == 'about') {
            $pageData = Page::get($page, $id);
        } else {
            $pageData = null;
        }

        return new Response(View::render(
            'channel/index',
            array(
                'channel'     => $channel,
                'page'     => $pageData,

                // centrales
                'order'    => $order,
                    'posts'    => $posts,
                    'promotes' => $promotes,
                    'calls'    => array('calls'=>$calls, 'campaigns'=>$campaigns),
                    'patrons' => $patrons,

                // laterales
                'side_order' => $side_order,
                    'searcher' => $searcher,
                    'categories' => $categories,
                    'summary'  => $summary,
                    'sumcalls' => $sumcalls,
                    'sponsors' => $sponsors,

                // iconos de recompensas
                'icons' => $icons,

                // si hay que ocultar los destacados (por ser el about)
                'hide_promotes' => $hide_promotes,

                // los ocultos del discover
                'discover' => $discover

            )
        ));
    }

}