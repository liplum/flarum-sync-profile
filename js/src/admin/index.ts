import app from 'flarum/admin/app';
import { extName } from '../r';

app.initializers.add(extName, () => {
  app.extensionData
    .for(extName)
    .registerSetting({
      setting: `${extName}.syncUsersEndpoint`,
      label: app.translator.trans(`${extName}.admin.syncUsersEndpoint.label`),
      help: app.translator.trans(`${extName}.admin.syncUsersEndpoint.help`),
      type: `text`
    })
    .registerSetting({
      setting: `${extName}.syncUserEndpoint`,
      label: app.translator.trans(`${extName}.admin.syncUserEndpoint.label`),
      help: app.translator.trans(`${extName}.admin.syncUserEndpoint.help`),
      type: `text`
    })
    .registerSetting({
      setting: `${extName}.authorizationHeader`,
      label: app.translator.trans(`${extName}.admin.authorizationHeader.label`),
      help: app.translator.trans(`${extName}.admin.authorizationHeader.help`),
      type: `text`
    })
    .registerSetting({
      setting: `${extName}.webhookToken`,
      label: app.translator.trans(`${extName}.admin.webhookToken.label`),
      help: app.translator.trans(`${extName}.admin.webhookToken.help`),
      type: `text`
    })
});
