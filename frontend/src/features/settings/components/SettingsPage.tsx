import React from "react";

export const SettingsPage: React.FC = () => {
  return (
    <div className="space-y-6" id="settings-page">
      <div className="flex flex-col gap-2">
        <h1 className="text-3xl font-bold tracking-tight text-foreground">Settings</h1>
        <p className="text-muted-foreground">Manage your account credentials, notifications, and application preferences.</p>
      </div>
      <div className="rounded-xl border border-border bg-card p-6 shadow-sm">
        <div className="h-60 rounded-lg border border-dashed border-border flex items-center justify-center text-sm text-muted-foreground">
          System settings form placeholder
        </div>
      </div>
    </div>
  );
};

export default SettingsPage;
