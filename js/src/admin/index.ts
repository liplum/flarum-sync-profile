import app from 'flarum/admin/app';

app.initializers.add('liplum/sync-profile', () => {
  app.extensionData
    .for('liplum-sync-profile')
    .registerSetting({
      setting: 'liplum-sync-profile.syncSingleUserEndpoint',
      label: app.translator.trans('liplum-sync-profile.admin.syncSingleUserEndpoint.label'),
      help: app.translator.trans('liplum-sync-profile.admin.syncSingleUserEndpoint.help'),
      type: 'text'
    })
    .registerSetting({
      setting: 'liplum-sync-profile.syncMultipleUserEndpoint',
      label: app.translator.trans('liplum-sync-profile.admin.syncMultipleUserEndpoint.label'),
      help: app.translator.trans('liplum-sync-profile.admin.syncMultipleUserEndpoint.help'),
      type: 'text'
    })
});
