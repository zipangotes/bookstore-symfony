<?php

namespace AppBundle\Controller;

use CommonBundle\Behavior\Controller\Pagination as PaginationTrait;
use DataBundle\Repository\BookRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BooksController extends Controller
{
    use PaginationTrait;

    /**
     * @Route("/books", name="books_index")
     * @Template(":AppBundle:Books/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $search = $request->get('search');
        $genre = $request->get('genre');
        $author = $request->get('author');

        $booksPerPage = (int) $request->get('books_per_page', $this->getParameter('books_per_page_default'));
        if ($booksPerPage > $this->getParameter('books_per_page_max')) {
            $booksPerPage = $this->getParameter('books_per_page_max');
        }

        $page = $request->get('page', 1);

        $query = $this->getBookRepository()->findBooksQueryBuilder($search, ['genre' => $genre, 'author' => $author]);
        $books = new Pagerfanta(new DoctrineORMAdapter($query));
        $books
            ->setMaxPerPage($booksPerPage)
            ->setCurrentPage($page)
        ;

        // todo: cache
        $genres = $this->getDoctrine()->getRepository('DataBundle:Genre')->findAll();
        $authors = $this->getDoctrine()->getRepository('DataBundle:Author')->findAll();

        return [
            'genres' => $genres,
            'authors' => $authors,
            'books' => $books,
            'booksPerPage' => $booksPerPage,
            'pagination' => $this->getPagination($page, $booksPerPage, $books->getNbResults())
        ];
    }

    /**
     * @return BookRepository
     */
    protected function getBookRepository()
    {
        return $this->getDoctrine()->getRepository('DataBundle:Book');
    }

}
