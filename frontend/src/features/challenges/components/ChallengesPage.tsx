import React from "react";

export const ChallengesPage: React.FC = () => {
  return (
    <div className="space-y-6" id="challenges-page">
      <div className="flex flex-col gap-2">
        <h1 className="text-3xl font-bold tracking-tight text-foreground">Challenges</h1>
        <p className="text-muted-foreground">Join community challenges, complete quests, and compete with friends.</p>
      </div>
      <div className="rounded-xl border border-border bg-card p-6 shadow-sm">
        <div className="h-60 rounded-lg border border-dashed border-border flex items-center justify-center text-sm text-muted-foreground">
          Active & Public Challenges Grid Placeholder
        </div>
      </div>
    </div>
  );
};

export default ChallengesPage;
