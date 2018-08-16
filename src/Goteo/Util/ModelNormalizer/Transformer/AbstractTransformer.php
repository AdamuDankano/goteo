<?php
/*
 * This file is part of the Goteo Package.
 *
 * (c) Platoniq y Fundación Goteo <fundacion@goteo.org>
 *
 * For the full copyright and license information, please view the README.md
 * and LICENSE files that was distributed with this source code.
 */
namespace Goteo\Util\ModelNormalizer\Transformer;

use Goteo\Core\Model;
use Goteo\Model\Image;
use Goteo\Library\Text;
use Goteo\Model\User;

abstract class AbstractTransformer extends \ArrayObject implements TransformerInterface {
    protected $model;
    protected $user;
    protected $keys = ['id', 'name'];

    public function __construct(Model $model, array $keys = null) {
        $this->model = $model;
        if($keys) {
            $this->setKeys($keys);
        }
    }

    public function rebuild() {
        // Default iterable
        foreach($this->getKeys() as $key) {
            $func = 'get' . ucfirst($key);
            $this->offsetSet($key, $this->$func());
        }
        return $this;
    }


    public function getModelName() {
        $class = get_class($this->model);
        return basename(str_replace('\\', '/', strtolower($class)));
    }

    public function setUser(User $user = null) {
        $this->user = $user;
        return $this;
    }

    public function getUser() {
        return $this->user;
    }

    public function setKeys(array $keys) {
        $this->keys = $keys;
        return $this;
    }

    public function getKeys() {
        return $this->keys;
    }

    public function getLabel($key) {
        return Text::get("admin-title-$key");
    }

    public function getValue($key) {
        if($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }
        return '';
    }

    public function getId() {
        return $this->model->id;
    }

    public function getName() {
        return $this->model->name ? $this->model->name : $this->model->title;
    }

    public function getLink($type = 'public', $key = null) {
        return '';
    }

    /** Optional methods */

    public function getImage() {
        return Image::get($this->model->image)->getLink(64, 64, true);
    }

    public function getTitle() {
        return $this->model->title;
    }

    public function getSubTitle() {
        return $this->model->subtitle;
    }

    public function getDate() {
        return \date_formater($this->model->date);
    }

    public function getActions() {
        if(!$u = $this->getUser()) return [];
        $ret = ['edit' => '/admin/' . $this->getModelName() . '/edit/' . $this->model->getId()];
        return $ret;
    }
}
