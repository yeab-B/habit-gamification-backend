import React from "react";

export const ProfilePage: React.FC = () => {
  return (
    <div className="space-y-6" id="profile-page">
      <div className="flex flex-col gap-2">
        <h1 className="text-3xl font-bold tracking-tight text-foreground">User Profile</h1>
        <p className="text-muted-foreground">View your achievements, statistics, levels, and customize your profile card.</p>
      </div>
      <div className="rounded-xl border border-border bg-card p-6 shadow-sm">
        <div className="h-60 rounded-lg border border-dashed border-border flex items-center justify-center text-sm text-muted-foreground">
          Profile stats card, Level indicator & Achievements grid placeholder
        </div>
      </div>
    </div>
  );
};

export default ProfilePage;
