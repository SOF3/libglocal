// This is a generated file. Not intended for manual editing.
package io.github.sof3.libglocal.intellij.psi.impl;

import java.util.List;
import org.jetbrains.annotations.*;
import com.intellij.lang.ASTNode;
import com.intellij.psi.PsiElement;
import com.intellij.psi.PsiElementVisitor;
import com.intellij.psi.util.PsiTreeUtil;
import static io.github.sof3.libglocal.intellij.parser.LgcElements.*;
import com.intellij.extapi.psi.ASTWrapperPsiElement;
import io.github.sof3.libglocal.intellij.psi.*;

public class LgcMathComparatorImpl extends ASTWrapperPsiElement implements LgcMathComparator {

  public LgcMathComparatorImpl(@NotNull ASTNode node) {
    super(node);
  }

  public void accept(@NotNull LgcVisitor visitor) {
    visitor.visitMathComparator(this);
  }

  public void accept(@NotNull PsiElementVisitor visitor) {
    if (visitor instanceof LgcVisitor) accept((LgcVisitor)visitor);
    else super.accept(visitor);
  }

  @Override
  @Nullable
  public PsiElement getMathEq() {
    return findChildByType(MATH_EQ);
  }

  @Override
  @Nullable
  public PsiElement getMathGe() {
    return findChildByType(MATH_GE);
  }

  @Override
  @Nullable
  public PsiElement getMathGt() {
    return findChildByType(MATH_GT);
  }

  @Override
  @Nullable
  public PsiElement getMathLe() {
    return findChildByType(MATH_LE);
  }

  @Override
  @Nullable
  public PsiElement getMathLt() {
    return findChildByType(MATH_LT);
  }

  @Override
  @Nullable
  public PsiElement getMathNe() {
    return findChildByType(MATH_NE);
  }

}
