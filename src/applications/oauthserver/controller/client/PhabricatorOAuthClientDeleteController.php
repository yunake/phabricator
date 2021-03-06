<?php

/*
 * Copyright 2012 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * @group oauthserver
 */
final class PhabricatorOAuthClientDeleteController
extends PhabricatorOAuthClientBaseController {

  public function processRequest() {
    $phid          = $this->getClientPHID();
    $title         = 'Delete OAuth Client';
    $request       = $this->getRequest();
    $current_user  = $request->getUser();
    $client = id(new PhabricatorOAuthServerClient())
      ->loadOneWhere('phid = %s',
                     $phid);

    if (empty($client)) {
      return new Aphront404Response();
    }
    if ($client->getCreatorPHID() != $current_user->getPHID()) {
      $message = 'Access denied to client with phid '.$phid.'. '.
                 'Only the user who created the client has permission to '.
                 'delete the client.';
      return id(new Aphront403Response())
        ->setForbiddenText($message);
    }

    if ($request->isFormPost()) {
      $client->delete();
      return id(new AphrontRedirectResponse())
        ->setURI('/oauthserver/client/?deleted=1');
    }

    $client_name = phutil_escape_html($client->getName());
    $title .= ' '.$client_name;

    $dialog = new AphrontDialogView();
    $dialog->setUser($current_user);
    $dialog->setTitle($title);
    $dialog->appendChild(
      '<p>Are you sure you want to delete this client?</p>'
    );
    $dialog->addSubmitButton();
    $dialog->addCancelButton($client->getEditURI());
    return id(new AphrontDialogResponse())->setDialog($dialog);

  }
}
