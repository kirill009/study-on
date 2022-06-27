<?php

namespace App\DataFixtures;

use App\Entity\Course;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CourseFixtures extends Fixture
{
    private static $data = [
        0 => [
            "name" => "Введение в робототехнику",
            "description" => "На курсе «Введение в робототехнику» обучающиеся познакомятся с основами конструирования и программирования робототехнических устройств, научатся удалённо управлять роботом и объединять нескольких роботов в сеть для решения общей задачи, реализуют распознавание ARTag меток с помощь камеры."
        ],
        1 => [
            "name" => "Цифровая трансформация промышленных предприятий",
            "description" => "Курс познакомит слушателей с подходами цифровизации производственных процессов, бизнес-процессов компании и к управлению предприятием на основе предиктивной аналитики данных."
        ],
        2 => [
            "name" => "Управление данными",
            "description" => "Курс посвящен современным технологиям управления данными. Студенты получат знания о теории баз данных, умения и навыки информационного моделирования, проектирования и эксплуатации баз данных."
        ]
    ];

    public function load(ObjectManager $manager): void
    {
        $transliterator = \Transliterator::create('Any-Latin');
        $transliteratorToASCII = \Transliterator::create('Latin-ASCII');

        foreach (self::$data as $key=> $datum)
        {
            $courseCode = $transliteratorToASCII->transliterate($transliterator->transliterate($datum["name"]));

            $course = new Course();
            $course->setName($datum["name"]);
            $course->setCode($courseCode);
            $course->setDescription($datum["description"]);
            $manager->persist($course);
            $manager->flush();

            $this->addReference(sprintf('course-%s', $key), $course);
        }
    }
}
