<?php

/**
 * Created by PhpStorm.
 * User: farhan.gul
 * Date: 5/29/17
 * Time: 4:51 PM
 */
class MyProfileController extends ProfileController
{
    public function actionPublishPost() {

        $post = new Events;
        // $user = $this->loadModel($id);
        if (isset($_POST['text']) && $_POST['text'] != "") {
            $post->text = $_POST['text'];
            $post->visibility = $_POST['visibility'];
            if (isset($_POST['associationId'])) {
                $post->associationId = $_POST['associationId'];
                $post->associationType = 'User';
            }
            //$soc->attributes = $_POST['Social'];
            //die(var_dump($_POST['Social']));
            $location = Yii::app()->params->profile->user->logLocation('activityPost', 'POST');
            $geoCoords = isset($_POST['geoCoords']) ? CJSON::decode($_POST['geoCoords'], true) : null;
            $isCheckIn = ($geoCoords && (isset($geoCoords['lat']) || isset($geoCoords['locationEnabled'])));
            if ($location && $isCheckIn) {
                // Only associate location when a checkin is requested
                $post->locationId = $location->id;
                $staticMap = $location->generateStaticMap();
                $post->text .= '$|&|$' . $geoCoords['comment'] . '$|&|$'; //temporary dividers to be parsed later
                $geocodedAddress = isset($geoCoords['address']) ? $geoCoords['address'] : $location->geocode();
            }
            if (isset($_POST['recordLinks']) && ($decodedLinks = CJSON::decode($_POST['recordLinks'], true)))
                $post->recordLinks = $decodedLinks;
            $post->user = Yii::app()->user->getName();
            $post->type = 'feed';
            $post->subtype = $_POST['subtype'];
            $post->lastUpdated = time();
            $post->timestamp = time();

            //custom code
            $post->test_field1 = $_POST['test_field1'];

            if ($post->save()) {
                if (!empty($staticMap)) {
                    $post->type = 'media';
                    if (!empty($geocodedAddress)) {
                        $post->text .= Yii::t('app', 'Checking in at ').$geocodedAddress.' | '.
                            Formatter::formatDateTime(time());
                    }
                    $post->saveRaw(Yii::app()->params->profile, $staticMap);
                }
                if (!empty($post->associationId) &&
                    $post->associationId != Yii::app()->user->getId() &&
                    $post->isVisibleTo (User::model ()->findByPk ($post->associationId))) {

                    $notif = new Notification;

                    $notif->type = 'social_post';
                    $notif->createdBy = $post->user;
                    $notif->modelType = 'Profile';
                    $notif->modelId = $post->associationId;

                    $notif->user = Yii::app()->db->createCommand()
                        ->select('username')
                        ->from('x2_users')
                        ->where('id=:id', array(':id' => $post->associationId))
                        ->queryScalar();

                    // $prof = X2Model::model('Profile')->findByAttributes(array('username'=>$post->user));
                    // $notif->text = "$prof->fullName posted on your profile.";
                    // $notif->record = "profile:$prof->id";
                    // $notif->viewed = 0;
                    $notif->createDate = time();
                    // $subject=X2Model::model('Profile')->findByPk($id);
                    // $notif->user = $subject->username;
                    $notif->save();
                }
            }
        }
    }

    public function actionUpdatePost($id, $profileId) {
        $post = Events::model()->findByPk($id);
        if (isset($_POST['Events'])) {
            $post->text = $_POST['Events']['text'];
            $post->test_field1 = $_POST['Events']['test_field1'];
            $post->save();
            $this->redirect(array('view', 'id' => $profileId));
            //$this->redirect(array('/profile/profile'));
        }
        $commentDataProvider = new CActiveDataProvider('Events', array(
            'criteria' => array(
                'order' => 'timestamp ASC',
                'condition' => "type='comment' AND associationType='Events' AND associationId=$id",
            )));
        $this->render('updatePost', array(
            'id' => $id,
            'model' => $post,
            'commentDataProvider' => $commentDataProvider,
            'profileId' => $profileId
        ));
    }

}