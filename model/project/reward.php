<?php

namespace Goteo\Model\Project {

    use \Goteo\Model\Icon,
        \Goteo\Model\License;

    class Reward extends \Goteo\Core\Model {

        public
                $id,
                $project,
                $reward,
                $description,
                $type = 'social',
                $icon,
                $other, // para el icono de otro, texto que diga el tipo
                $license,
                $amount,
                $units,
                $taken = 0, // recompensas comprometidas por aporte
                $none; // si no quedan unidades de esta recompensa

        public static function get($id) {
            try {
                $query = static::query("SELECT * FROM reward WHERE id = :id", array(':id' => $id));
                return $query->fetchObject(__CLASS__);
            } catch (\PDOException $e) {
                throw new \Goteo\Core\Exception($e->getMessage());
            }
        }

        public static function getAll($project, $type = 'social', $lang = null, $fulfilled = null, $icon = null) {
            try {
                $array = array();

                $values = array(
                    ':project' => $project,
                    ':type' => $type,
                    ':lang' => $lang,
                    ':web_lang' => \LANG
                );

                $sqlFilter = "";
                if (!empty($fulfilled)) {
                    $sqlFilter .= "    AND reward.fulsocial = :fulfilled";
                    $values[':fulfilled'] = $fulfilled == 'ok' ? 1 : 0;
                }
                if (!empty($icon)) {
                    $sqlFilter .= "    AND reward.icon = :icon";
                    $values[':icon'] = $icon;
                }

                $sql = "SELECT
                            reward.id as id,
                            reward.project as project,
                            IFNULL(reward_lang.reward, reward.reward) as reward,
                            IFNULL(reward_lang.description, reward.description) as description,
                            reward.type as type,
                            reward.icon as icon,
                            IFNULL(reward_lang.other, reward.other) as other,
                            reward.license as license,
                            reward.amount as amount,
                            reward.units as units,
                            reward.fulsocial as fulsocial,
                            IFNULL(icon_lang.name, icon.name) as icon_name,
                            url
                        FROM    reward
                        LEFT JOIN icon
                            ON icon.id = reward.icon
                        LEFT JOIN reward_lang
                            ON  reward_lang.id = reward.id
                            AND reward_lang.lang = :lang
                        LEFT JOIN icon_lang
                            ON  icon_lang.id = icon.id
                            AND icon_lang.lang = :web_lang
                        WHERE   project = :project
                            AND type= :type
                        $sqlFilter
                        ";

                $sql .= " ORDER BY reward.id ASC";

                $query = self::query($sql, $values);
                foreach ($query->fetchAll(\PDO::FETCH_CLASS, __CLASS__) as $item) {

                    if ($item->icon == 'other' && !empty($item->other)) {
                        $item->icon_name = $item->other;
                    }

                    $array[$item->id] = $item;
                }
                return $array;
            } catch (\PDOException $e) {
                throw new \Goteo\Core\Exception($e->getMessage());
            }
        }

        public function validate(&$errors = array()) {
            // Estos son errores que no permiten continuar
            if (empty($this->project))
                $errors[] = 'No hay proyecto al que asignar la recompensa/rettorno';
            //Text::get('validate-reward-noproject');
            /*
              if (empty($this->reward))
              $errors[] = 'No hay nombre de recompensa/retorno';
              //Text::get('validate-reward-name');

              if (empty($this->type))
              $errors[] = 'No hay tipo de recompensa/retorno';
              //Text::get('validate-reward-description');
             */
            //cualquiera de estos errores hace fallar la validación
            if (!empty($errors))
                return false;
            else
                return true;
        }

        public function save(&$errors = array()) {
            if (!$this->validate($errors))
                return false;

            $fields = array(
                'id',
                'project',
                'reward',
                'description',
                'type',
                'icon',
                'other',
                'license',
                'amount',
                'units'
            );

            $set = '';
            $values = array();

            foreach ($fields as $field) {
                if ($set != '')
                    $set .= ", ";
                $set .= "$field = :$field ";
                $values[":$field"] = $this->$field;
            }

            try {
                $sql = "REPLACE INTO reward SET " . $set;
                self::query($sql, $values);
                if (empty($this->id))
                    $this->id = self::insertId();
                return true;
            } catch (\PDOException $e) {
                $errors[] = "El retorno {$this->reward} no se ha grabado correctamente. Por favor, revise los datos." . $e->getMessage();
                return false;
            }
        }

        public function saveLang(&$errors = array()) {

            $fields = array(
                'id' => 'id',
                'lang' => 'lang',
                'reward' => 'reward_lang',
                'description' => 'description_lang',
                'other' => 'other_lang'
            );

            $set = '';
            $values = array();

            foreach ($fields as $field => $ffield) {
                if ($set != '')
                    $set .= ", ";
                $set .= "$field = :$field ";
                $values[":$field"] = $this->$ffield;
            }

            try {
                $sql = "REPLACE INTO reward_lang SET " . $set;
                self::query($sql, $values);

                return true;
            } catch (\PDOException $e) {
                $errors[] = "El retorno {$this->reward} no se ha grabado correctamente. Por favor, revise los datos." . $e->getMessage();
                return false;
            }
        }

        /**
         * Quitar un retorno de un proyecto
         *
         * @param varchar(50) $project id de un proyecto
         * @param INT(12) $id  identificador de la tabla reward
         * @param array $errors
         * @return boolean
         */
        public function remove(&$errors = array()) {
            $values = array(
                ':project' => $this->project,
                ':id' => $this->id,
            );

            try {
                self::query("DELETE FROM reward WHERE id = :id AND project = :project", $values);
                return true;
            } catch (\PDOException $e) {
                $errors[] = 'No se ha podido quitar el retorno ' . $this->id . '. ' . $e->getMessage();
                //Text::get('remove-reward-fail');
                return false;
            }
        }

        /**
         * Calcula y actualiza las unidades de recompensa comprometidas por aporte
         * @param void
         * @return numeric
         */
        public function getTaken() {

            // cuantas de esta recompensa en aportes no cancelados
            $sql = "SELECT
                        COUNT(invest_reward.reward) as taken
                    FROM invest_reward
                    INNER JOIN invest
                        ON invest.id = invest_reward.invest
                        AND invest.status IN ('0', '1', '3', '4')
                        AND invest.project = :project
                    WHERE invest_reward.reward = :reward
                ";

            $values = array(
                ':project' => $this->project,
                ':reward' => $this->id
            );

            $query = self::query($sql, $values);
            if ($taken = $query->fetchColumn(0)) {
                return $taken;
            } else {
                return 0;
            }
        }

        public static function icons($type = 'social') {
            $list = array();

            $icons = Icon::getAll($type);

            foreach ($icons as $icon) {
                $list[$icon->id] = $icon;
            }

            return $list;
        }

        public static function licenses() {
            $list = array();

            $licenses = License::getAll();

            foreach ($licenses as $license) {
                $list[$license->id] = $license->name;
            }

            return $list;
        }

        /* para saber si ha cumplido con recompensas/retornos */
        public static function areFulfilled($project, $type = 'individual') {

            // diferente segun tipo
            if ($type == 'social') {
                $sql = "SELECT
                            COUNT(id)
                        FROM reward
                        WHERE project = :project
                        AND type = 'social'
                        AND fulsocial != 1
                    ";
            } else {
                $sql = "SELECT
                            COUNT(invest_reward.reward)
                        FROM invest_reward
                        INNER JOIN invest
                            ON invest.id = invest_reward.invest
                            AND invest.status IN ('1', '3')
                            AND invest.project = :project
                        WHERE invest_reward.fulfilled != 1
                    ";
            }

            $values = array(
                ':project' => $project
            );

            $query = self::query($sql, $values);
            $fulfilled = $query->fetchColumn(0);
            if ($fulfilled == 0) {
                return true;
            } else {
                return false;
            }
        }
        
        public static function getChossen($filters = array()) {
            try {
                $array = array();

                $values = array();

                $sqlFilter = "";
                if (!empty($filters['project'])) {
                    $sqlFilterProj = " AND project.id = :project";
                    $values[':project'] = $filters['project'];
                }
                if (!empty($filters['name'])) {
                    $sqlFilterUser = " AND (user.name LIKE :name OR user.email LIKE :name)";
                    $values[':name'] = "%{$filters['name']}%";
                }
                if (!empty($filters['status'])) {
                    $sqlFilter = " WHERE invest_reward.fulfilled = :status";
                    $values[':status'] = $filters['status'] == 'ok' ? 1 : 0;
                }

                $sql = "SELECT
                            invest_reward.invest as invest,
                            reward.reward as reward_name,
                            user.id as user,
                            user.name as name,
                            user.email as email,
                            reward.project as project,
                            invest_reward.fulfilled as fulfilled,
                            invest_reward.reward as reward,
                            invest.amount as amount
                        FROM invest_reward
                        INNER JOIN invest
                            ON invest.id = invest_reward.invest
                            AND invest.status IN (0, 1, 3)
                        INNER JOIN user
                            ON user.id = invest.user
                            $sqlFilterUser
                        INNER JOIN project
                            ON project.id = invest.project
                            AND project.status IN (3, 4, 5)
                            $sqlFilterProj
                        INNER JOIN reward
                            ON reward.id = invest_reward.reward
                        $sqlFilter
                        ";

                $sql .= " ORDER BY user.name ASC";

                $query = self::query($sql, $values);
                foreach ($query->fetchAll(\PDO::FETCH_OBJ) as $item) {

                    $array[$item->invest] = $item;
                }
                return $array;
            } catch (\PDOException $e) {
                throw new \Goteo\Core\Exception($e->getMessage());
            }
        }

        /*
         * Método simple para sacar la lista de recompensas de un aporte
         */        
        public static function txtRewards($invest) {
            try {
                $array = array();

                $sql = "SELECT
                            reward.reward as name
                        FROM invest_reward
                        INNER JOIN reward
                            ON reward.id = invest_reward.reward
                        WHERE invest_reward.invest = ?
                        ORDER BY reward.amount ASC
                        ";

                $query = self::query($sql, array($invest));
                foreach ($query->fetchAll(\PDO::FETCH_OBJ) as $item) {
                    $array[] = $item->name;
                }
                return implode(', ', $array);

            } catch (\PDOException $e) {
                return '';
            }
        }

        
    }

}