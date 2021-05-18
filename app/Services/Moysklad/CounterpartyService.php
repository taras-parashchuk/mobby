<?php

namespace App\Services\Moysklad;

use App\Models\Language;
use App\Models\Setting;
use App\Models\UserExternalSource;
use App\Models\UserGroup;
use App\Models\UserGroupTranslation;
use App\User;
use Illuminate\Database\Eloquent\Builder;

class CounterpartyService
{
    /**
     * Create new counterparty in moy-sklad from the model
     *
     * @param array $user
     * @param null $sync
     * @return UserExternalSource
     */
    public function create($user = [], $sync = null)
    {
        $client = new Client();
        $data = [];

        if (empty($user)) {

            $userGroupId = Setting::get('user_group_after_register');
            $userGroupTranslation = UserGroupTranslation::where('user_group_id', $userGroupId)->where('locale', Setting::get('admin_language'))->first();

            $data = [
                'name' => 'helpwizor',
                'tags' => [
                    $userGroupTranslation->name
                ]
            ];

        } elseif ($user->fullName) {
            $userGroupTranslation = UserGroupTranslation::where('user_group_id', $user->group_id)->where('locale', Setting::get('admin_language'))->first();

            $data = [
                'name' => $user->fullName,
                'tags' => [
                    $userGroupTranslation->name
                ]
            ];

            if (!is_null($user->email) && $user->email) {
                $data['email'] = $user->email;
            }

            if (!is_null($user->telephone) && $user->telephone) {
                $data['phone'] = $user->telephone;
            }
        }

        try {
            $res = $client->postGuzzle('counterparty', [
                'json' => $data
            ]);

            $externalUser = json_decode($res->getBody()->getContents());

            if (!empty($user)) {
                $userExternalSource = new UserExternalSource([
                    'external_user_id' => $externalUser->id,
                    'external_type' => config('syncs.moysklad.externalCode')
                ]);

                $userExternalSource = $user->externalSource()->save($userExternalSource);
                return $userExternalSource;
            }

            return $externalUser;

        } catch (\Exception $exception) {
            if (!is_null($sync)) {
                Service::exception($exception, $sync);
            }
        }
    }

    /**
     * Create new counterparty in moy-sklad
     *
     * @param $name
     * @param $email
     * @param $phone
     * @return mixed
     */
    public function createCounterparty($name, $email, $phone)
    {
        $client = new Client();

        $data = [
            'name' => $name,
        ];

        if (!is_null($email)) {
            $data['email'] = $email;
        }

        if (!is_null($phone)) {
            $data['phone'] = $phone;
        }

        try {
            $res = $client->postGuzzle('counterparty', [
                'json' => $data
            ]);
        } catch (\Exception $exception) {
            Service::exception($exception);
        }

        $external_user_id = json_decode($res->getBody()->getContents())->id;

        return $external_user_id;
    }

    /**
     * Get helpwizor counterparty
     *
     * @return UserExternalSource|mixed|null
     * @throws \Throwable
     */
    public function getHelpwizorCounterparty()
    {
        $helpwizorCounterparty = null;
        $dbUser = User::where('firstname', 'helpwizor')
            ->whereHas('externalSource', function (Builder $query) {
                $query->where('external_type', config('syncs.moysklad.externalCode'));
            })->with('externalSource')->first();

        if (is_null($dbUser)) {
            $helpwizorCounterparty = $this->create();

            // save to database
            $dbUser = User::where('firstname', 'helpwizor')->first();
            if (!is_null($dbUser)) {
                $dbUser->delete();
            }
            self::storeDBCounterparty($helpwizorCounterparty);
        } else {
            $helpwizorCounterparty = self::getCounterparty($dbUser->externalSource[0]->external_user_id);
            if (is_null($helpwizorCounterparty)) {
                $helpwizorCounterparty = $this->create();

                // save to database
                $dbUser = User::where('firstname', 'helpwizor')->first();
                if (!is_null($dbUser)) {
                    $dbUser->delete();
                }
                self::storeDBCounterparty($helpwizorCounterparty);
            }
        }

        return $helpwizorCounterparty;
    }

    /**
     * Get counterparty from moy-sklad
     *
     * @param $external_user_id
     * @return mixed|null
     */
    public static function getCounterparty($external_user_id)
    {
        $client = new Client();
        $url = 'counterparty/' . $external_user_id;

        try {
            $res = $client->getGuzzle($url);
        } catch (\Exception $exception) {
            Service::exception($exception);
            return null;
        }
        $externalUser = json_decode($res->getBody()->getContents());

        return $externalUser;
    }

    /**
     * Get all counterparties from moy-sklad
     *
     * @param $sync
     * @param int $limit
     * @return array
     */
    public static function getCounterparties($sync, $offset = 0, $limit = 25)
    {
        $counterparties = [];
        $client = new Client();

        try {
            $res = $client->getGuzzle('counterparty', [
                'query' => [
                    'offset' => $offset,
                    'limit' => $limit
                ]
            ]);
        } catch (\Exception $exception) {
            Service::exception($exception, $sync);
            return $counterparties;
        }

        $counterparties_info = json_decode($res->getBody()->getContents());

        $counterparties['rows'] = $counterparties_info->rows;
        $counterparties['total'] = $counterparties_info->meta->size;

        return $counterparties;
    }

    /**
     * Create new user in database
     *
     * @param $counterparty
     * @throws \Throwable
     */
    public static function storeDBCounterparty($counterparty)
    {
        $dbUser = new User();
        $userGroupId = Setting::get('user_group_after_register');

        if (!empty($counterparty->tags)) {
            $userGroupTranslation = UserGroupTranslation::where('name', $counterparty->tags[0])->first();

            if (is_null($userGroupTranslation)) {
                $userGroup = UserGroup::create([
                    'key' => ''
                ]);

                $translations = [];
                foreach (Language::getOnlyActive() as $language) {
                    $translations[] = new UserGroupTranslation(
                        [
                            'name' => $counterparty->tags[0],
                            'locale' => $language['locale']
                        ]
                    );
                }
                $userGroup->translates()->saveMany($translations);
                $userGroupId = $userGroup->id;
            } else {
                $userGroupId = $userGroupTranslation->user_group_id;
            }
        }

        $dbUser->firstname = $counterparty->name;
        $dbUser->email = property_exists($counterparty, 'email') ? $counterparty->email : null;
        $dbUser->telephone = property_exists($counterparty, 'phone') ? $counterparty->phone : null;
        $dbUser->group_id = $userGroupId;

        $userExternalSource = new UserExternalSource([
            'external_user_id' => $counterparty->id,
            'external_type' => config('syncs.moysklad.externalCode')
        ]);

        \DB::transaction(function () use ($dbUser, $userExternalSource) {

            $dbUser->save();

            $dbUser->externalSource()->save($userExternalSource);

        });
    }

    /**
     * Update counterparty on moy-sklad according to the parameters
     *
     * @param $counterparty
     * @param $sync
     * @return bool
     */
    public static function updateMoyskladCounterparty($counterparty, $sync = null)
    {
        $parameters = Service::getUpdateParameters('upload', config('syncs.moysklad.dataTypes.user'), $sync);
        $client = new Client();
        $externalSource = $counterparty->externalSource()->where('external_type', config('syncs.moysklad.externalCode'))->first();
        $path = 'counterparty/' . $externalSource->external_user_id;
        $data = [];

        if (empty($parameters)) {
            return false;
        }

        if (isset($parameters['name']) && $parameters['name']) {
            $data['name'] = $counterparty->fullName;
        }

        if (isset($parameters['email']) && $parameters['email']) {
            if (!is_null($counterparty->email)) {
                $data['email'] = $counterparty->email;
            }
        }

        if (isset($parameters['phone_number']) && $parameters['phone_number']) {
            if (!is_null($counterparty->telephone)) {
                $data['phone'] = $counterparty->telephone;
            }
        }

        try {
            $client->putGuzzle($path, [
                'json' => $data
            ]);
        } catch (\Exception $exception) {
            Service::exception($exception, $sync);
        }
    }

    /**
     * Update counterparty from moy-sklad to database according to the parameters
     *
     * @param $counterparty
     * @param $dbCounterparty
     * @return bool
     */
    public static function updateDBCounterparty($counterparty, $dbCounterparty, $sync)
    {
        $parameters = Service::getUpdateParameters('download', config('syncs.moysklad.dataTypes.user'), $sync);
        $explodeName = explode(' ', $counterparty->name);

        if (empty($parameters)) {
            return false;
        }

        if (isset($parameters['first_name']) && $parameters['first_name']) {
            $dbCounterparty->firstname = $explodeName[0];
        }

        if (isset($parameters['last_name']) && $parameters['last_name']) {
            $dbCounterparty->lastname = $explodeName[1] ?? null;
        }

        if (isset($parameters['email']) && $parameters['email'] && property_exists($counterparty, 'email')) {
            $dbCounterparty->email = $counterparty->email;
        }

        if (isset($parameters['phone_number']) && $parameters['phone_number'] && property_exists($counterparty, 'phone')) {
            $dbCounterparty->telephone = $counterparty->phone;
        }

        $dbCounterparty->update();
    }
}
