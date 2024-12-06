import app from 'flarum/admin/app';

app.initializers.add('liplum/sync-profile', () => {
  app.extensionData
    .for('liplum-sync-profile')
    .registerSetting({
      setting: 'liplum-sync-profile.syncUsersEndpoint',
      label: app.translator.trans('liplum-sync-profile.admin.syncUsersEndpoint.label'),
      help: app.translator.trans('liplum-sync-profile.admin.syncUsersEndpoint.help'),
      type: 'text'
    })
    .registerSetting({
      setting: 'liplum-sync-profile.syncUserEndpoint',
      label: app.translator.trans('liplum-sync-profile.admin.syncUserEndpoint.label'),
      help: app.translator.trans('liplum-sync-profile.admin.syncUserEndpoint.help'),
      type: 'text'
    })
    .registerSetting({
      setting: 'liplum-sync-profile.authorizationHeader',
      label: app.translator.trans('liplum-sync-profile.admin.authorizationHeader.label'),
      help: app.translator.trans('liplum-sync-profile.admin.authorizationHeader.help'),
      type: 'text'
    })
});
