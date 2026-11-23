import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface LoginForm {
    email: string;
    password: string;
    remember: boolean;
}

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm<LoginForm>({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title="Log in" />
            <main className="grid min-h-svh bg-[#f4f7fb] lg:grid-cols-[1.08fr_0.92fr]">
                <section className="relative hidden overflow-hidden bg-[#071a3d] lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16">
                    <div className="absolute inset-0 opacity-20" aria-hidden="true">
                        <div className="absolute -right-28 -top-24 h-96 w-96 rounded-full border border-blue-300/50" />
                        <div className="absolute -right-10 top-10 h-64 w-64 rounded-full border border-blue-300/40" />
                        <div className="absolute bottom-[-14rem] left-[-10rem] h-[32rem] w-[32rem] rounded-full border border-blue-300/30" />
                    </div>

                    <div className="relative z-10 flex items-center gap-3 text-white">
                        <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                            <svg viewBox="0 0 24 24" className="h-6 w-6 fill-white" aria-hidden="true">
                                <path d="M21.5 16.5v-1.7l-8-5V3.6c0-1-.8-1.8-1.8-1.8S9.9 2.6 9.9 3.6v6.2l-8 5v1.7l8-2.4v5.1l-2.2 1.7v1.4l4-1.1 4 1.1v-1.4l-2.2-1.7v-5.1l8 2.4Z" />
                            </svg>
                        </div>
                        <div>
                            <p className="text-lg font-semibold tracking-tight">Gonzaga Air</p>
                            <p className="text-xs text-blue-200">Passenger Reservation</p>
                        </div>
                    </div>

                    <div className="relative z-10 max-w-xl">
                        <p className="mb-5 text-sm font-semibold uppercase tracking-[0.25em] text-blue-300">Welcome aboard</p>
                        <h2 className="text-5xl font-semibold leading-[1.08] tracking-tight text-white xl:text-6xl">
                            Your journey starts here.
                        </h2>
                        <p className="mt-6 max-w-md text-base leading-7 text-blue-100/80">
                            Sign in to manage flights, reservations, and passenger details in one place.
                        </p>
                    </div>

                    <p className="relative z-10 text-xs text-blue-200/70">Safe travels. Smooth journeys.</p>
                </section>

                <section className="flex min-h-svh items-center justify-center px-6 py-12 sm:px-10">
                    <div className="w-full max-w-md">
                        <div className="mb-10 flex items-center gap-3 lg:hidden">
                            <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-[#071a3d] text-white">
                                <svg viewBox="0 0 24 24" className="h-6 w-6 fill-white" aria-hidden="true">
                                    <path d="M21.5 16.5v-1.7l-8-5V3.6c0-1-.8-1.8-1.8-1.8S9.9 2.6 9.9 3.6v6.2l-8 5v1.7l8-2.4v5.1l-2.2 1.7v1.4l4-1.1 4 1.1v-1.4l-2.2-1.7v-5.1l8 2.4Z" />
                                </svg>
                            </div>
                            <div>
                                <p className="font-semibold text-[#071a3d]">Gonzaga Air</p>
                                <p className="text-xs text-slate-500">Passenger Reservation</p>
                            </div>
                        </div>

                        <div className="mb-8">
                            <p className="mb-2 text-sm font-semibold text-blue-700">Welcome back</p>
                            <h1 className="text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Sign in to your account</h1>
                            <p className="mt-3 text-sm text-slate-500">Enter your details to continue.</p>
                        </div>

                        {status && (
                            <div className="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                                {status}
                            </div>
                        )}

                        <form className="space-y-5" onSubmit={submit}>
                            <div className="space-y-2">
                                <Label htmlFor="email" className="text-slate-700">Email address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="you@example.com"
                                    className="h-12 border-slate-200 bg-white text-slate-950 shadow-sm placeholder:text-slate-400 focus-visible:ring-blue-600"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="password" className="text-slate-700">Password</Label>
                                    {canResetPassword && (
                                        <TextLink href={route('password.request')} className="text-sm font-medium text-blue-700" tabIndex={5}>
                                            Forgot password?
                                        </TextLink>
                                    )}
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="Enter your password"
                                    className="h-12 border-slate-200 bg-white text-slate-950 shadow-sm placeholder:text-slate-400 focus-visible:ring-blue-600"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center space-x-3">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    checked={data.remember}
                                    onCheckedChange={(checked) => setData('remember', checked === true)}
                                    tabIndex={3}
                                />
                                <Label htmlFor="remember" className="cursor-pointer text-sm text-slate-600">Remember me</Label>
                            </div>

                            <Button
                                type="submit"
                                className="mt-2 h-12 w-full rounded-lg bg-[#0757c9] text-white hover:bg-[#064cae]"
                                tabIndex={4}
                                disabled={processing}
                            >
                                {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                Sign in
                            </Button>
                        </form>

                        <p className="mt-7 text-center text-sm text-slate-500">
                            Don't have an account?{' '}
                            <TextLink href={route('register')} className="font-semibold text-blue-700" tabIndex={6}>
                                Create account
                            </TextLink>
                        </p>
                    </div>
                </section>
            </main>
        </>
    );
}
