<?php

namespace App\DataFixtures;

use App\Entity\Lesson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LessonFixtures extends Fixture
{
    private static $data = [
        0 => [
            "name" => "Введение",
            "content" => "На курсе «Введение в робототехнику» обучающиеся познакомятся с основами конструирования и программирования робототехнических устройств, научатся удалённо управлять роботом и объединять нескольких роботов в сеть для решения общей задачи, реализуют распознавание ARTag меток с помощь камеры.",
            "course" => 0
        ],
        1 => [
            "name" => "Основы программирования робототехнического контроллера",
            "content" => "Курс познакомит слушателей с подходами цифровизации производственных процессов, бизнес-процессов компании и к управлению предприятием на основе предиктивной аналитики данных.",
            "course" => 0
        ],
        2 => [
            "name" => "Теория автоматического управления",
            "content" => "Курс посвящен современным технологиям управления данными. Студенты получат знания о теории баз данных, умения и навыки информационного моделирования, проектирования и эксплуатации баз данных.",
            "course" => 0
        ],
        3 => [
            "name" => "Основы цифровой трансформация промышленных предприятий",
            "content" => "На курсе «Введение в робототехнику» обучающиеся познакомятся с основами конструирования и программирования робототехнических устройств, научатся удалённо управлять роботом и объединять нескольких роботов в сеть для решения общей задачи, реализуют распознавание ARTag меток с помощь камеры.",
            "course" => 1
        ],
        4 => [
            "name" => "Промышленные предприятия будущего",
            "content" => "Курс познакомит слушателей с подходами цифровизации производственных процессов, бизнес-процессов компании и к управлению предприятием на основе предиктивной аналитики данных.",
            "course" => 1
        ],
        5 => [
            "name" => "Организация кибер-физических производственных систем",
            "content" => "Курс посвящен современным технологиям управления данными. Студенты получат знания о теории баз данных, умения и навыки информационного моделирования, проектирования и эксплуатации баз данных.",
            "course" => 1
        ],
        6 => [
            "name" => "Введение. Обобщенная архитектура систем баз данных",
            "content" => "На курсе «Введение в робототехнику» обучающиеся познакомятся с основами конструирования и программирования робототехнических устройств, научатся удалённо управлять роботом и объединять нескольких роботов в сеть для решения общей задачи, реализуют распознавание ARTag меток с помощь камеры.",
            "course" => 2
        ],
        7 => [
            "name" => "Этапы проектирования БД, понятие модели данных, обзор основных моделей данных",
            "content" => "Курс познакомит слушателей с подходами цифровизации производственных процессов, бизнес-процессов компании и к управлению предприятием на основе предиктивной аналитики данных.",
            "course" => 2
        ],
        8 => [
            "name" => "Реляционная модель данных: допустимые структуры, ограничения",
            "content" => "Курс посвящен современным технологиям управления данными. Студенты получат знания о теории баз данных, умения и навыки информационного моделирования, проектирования и эксплуатации баз данных.",
            "course" => 2
        ]
    ];

    public function load(ObjectManager $manager): void
    {

        foreach (self::$data as $datum)
        {
            $lesson = new Lesson();
            $lesson->setCourse($this->getReference(sprintf('course-%s', $datum["course"])));
            $lesson->setName($datum["name"]);
            $lesson->setContent($datum["content"]);
            $lesson->setNumber(mt_rand(1,1000));

            $manager->persist($lesson);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CourseFixtures::class,
        ];
    }
}
