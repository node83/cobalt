create table `users`
(
    `id`        int unsigned not null auto_increment,
    `username`  varchar(25)  not null,
    `password`  varchar(100) not null,
    `email`     varchar(150) not null,
    `staff`     tinyint(1)   not null default 0,
    `superuser` tinyint(1)   not null default 0,

    primary key (`id`),
    unique  key (`username`),
    unique  key (`email`)
);
