import React from "react";
import { Link } from "react-router-dom";

export const RegisterPage: React.FC = () => {
  return (
    <div className="flex min-h-screen items-center justify-center bg-background px-4 py-12 sm:px-6 lg:px-8" id="register-page">
      <div className="w-full max-w-md space-y-8 border border-border bg-card p-8 rounded-lg shadow-lg">
        <div>
          <h2 className="mt-6 text-center text-3xl font-extrabold tracking-tight text-foreground">
            Create an Account
          </h2>
          <p className="mt-2 text-center text-sm text-muted-foreground">
            Or{" "}
            <Link to="/login" className="font-medium text-primary hover:underline">
              sign in to your existing account
            </Link>
          </p>
        </div>
        <div className="mt-8 space-y-6">
          <div className="rounded-md border border-dashed border-border p-4 text-center text-sm text-muted-foreground">
            Registration Form Placeholder
          </div>
        </div>
      </div>
    </div>
  );
};

export default RegisterPage;
