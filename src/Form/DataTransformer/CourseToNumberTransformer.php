<?php

namespace App\Form\DataTransformer;

use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CourseToNumberTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  Course|null $course
     */
    public function transform($course): string
    {
        if (null === $course) {
            return '';
        }

        return $course->getId();
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $courseNumber
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($courseNumber): ?Course
    {
        // no issue number? It's optional, so that's ok
        if (!$courseNumber) {
            return null;
        }

        $course = $this->entityManager
            ->getRepository(Course::class)
            // query for the issue with this id
            ->find($courseNumber)
        ;

        if (null === $course) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'An issue with number "%s" does not exist!',
                $courseNumber
            ));
        }

        return $course;
    }
}